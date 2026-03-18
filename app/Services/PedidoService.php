<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Empleado;
use App\Models\User;
use App\Models\HistoricoPedido;
use App\Models\PedidoPago;
use App\Services\WhatsAppService;
use App\Events\PedidoActualizado;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function confirmarPago(Pedido $pedido)
    {
        DB::transaction(function () use ($pedido) {
            $pedido->update(['estado' => 'pagado']);

            // Crear registro de pago en caja
            $this->crearPagoDesdePedido($pedido);

            // Registrar en histórico
            $this->registrarHistorico($pedido, "Pago confirmado - {$this->obtenerDetalleMetodoPagoCliente($pedido)}");

            // Enviar WhatsApp al cliente
            $this->enviarConfirmacionPago($pedido);

            // Broadcast evento
            broadcast(new PedidoActualizado($pedido, 'pago_confirmado'));
        });
    }

    public function asignarEmpleado(Pedido $pedido, Empleado $empleado)
    {
        DB::transaction(function () use ($pedido, $empleado) {
            $pedido->empleados()->sync([$empleado->id]);
            $pedido->update(['estado' => 'asignado']);

            // Registrar en histórico
            $this->registrarHistorico($pedido, "Empleado asignado: {$empleado->nombres}");

            // Cargar relaciones necesarias para WhatsApp
            $pedido->load(['user.empresa.pais', 'detalles.producto']);
            $empleado->load(['user']);
            
            // Enviar notificaciones por WhatsApp usando Jobs
            try {
                \App\Jobs\SendOrderAssignmentNotification::dispatch($pedido, $empleado);
                
                // Procesar el Job inmediatamente si no hay worker activo
                try {
                    \Artisan::call('queue:work', ['--once' => true, '--quiet' => true]);
                } catch (\Exception $e) {
                    \Log::info('Queue worker no disponible, Job se procesará cuando esté activo');
                }
                
                \Log::info('Notificaciones de asignación enviadas', [
                    'pedido_id' => $pedido->id,
                    'empleado_id' => $empleado->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Error al enviar notificaciones de asignación', [
                    'pedido_id' => $pedido->id,
                    'empleado_id' => $empleado->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Broadcast evento
            broadcast(new PedidoActualizado($pedido, 'empleado_asignado'));
        });
    }

    public function cancelarPedido(Pedido $pedido, $motivo = null)
    {
        DB::transaction(function () use ($pedido, $motivo) {
            $pedido->update([
                'estado' => 'cancelado',
                'nota' => $motivo ? "Cancelado: {$motivo}" : 'Pedido cancelado'
            ]);

            // Devolver productos al almacén
            $this->devolverProductosAlmacen($pedido);

            // Registrar en histórico
            $this->registrarHistorico($pedido, "Pedido cancelado: " . ($motivo ?? 'Sin motivo especificado'));

            // Enviar WhatsApp al cliente
            $this->enviarCancelacionPedido($pedido, $motivo);

            // Broadcast evento
            broadcast(new PedidoActualizado($pedido, 'pedido_cancelado'));
        });
    }

    public function finalizarEntrega(Pedido $pedido)
    {
        $pedido->update(['estado' => 'entregado']);

        // Registrar en histórico
        $this->registrarHistorico($pedido, "Entrega finalizada");

        // Enviar confirmación de entrega
        $this->enviarConfirmacionEntrega($pedido);

        // Broadcast evento
        broadcast(new PedidoActualizado($pedido, 'entrega_finalizada'));
    }

    /**
     * Revertir un pedido (incluso si está pagado)
     * Permite al cliente notificar cambios o solicitar reversión
     * Integración completa: también revierte el pago en caja si existe
     */
    public function revertirPedido(Pedido $pedido, $motivo = null, $tipoReversion = 'cambio')
    {
        DB::transaction(function () use ($pedido, $motivo, $tipoReversion) {
            // Determinar el nuevo estado basado en el tipo de reversión
            $nuevoEstado = match($tipoReversion) {
                'cancelacion' => 'cancelado',
                'cambio' => 'en_revision',
                'devolucion' => 'devuelto',
                default => 'en_revision'
            };

            // Actualizar el pedido con el motivo de la reversión
            $notaActualizada = $pedido->nota ? $pedido->nota . "\n" : "";
            $notaActualizada .= now()->format('d/m/Y H:i') . " - ";
            $notaActualizada .= ucfirst($tipoReversion) . ": " . ($motivo ?? 'Sin motivo especificado');

            $pedido->update([
                'estado' => $nuevoEstado,
                'nota' => $notaActualizada
            ]);

            // Devolver productos al almacén (solo si el pedido estaba confirmado o entregado)
            if (in_array($pedido->estado, ['pagado', 'asignado', 'entregado'])) {
                $this->devolverProductosAlmacen($pedido);
            }

            // Revertir el pago en caja si el pedido estaba pagado
            if ($pedido->estado === 'pagado') {
                $this->revertirPagoEnCaja($pedido, $motivo, $tipoReversion);
            }

            // Registrar en histórico
            $this->registrarHistorico($pedido, "Pedido revertido - {$tipoReversion}: " . ($motivo ?? 'Sin motivo'));

            // Enviar notificaciones según el tipo de reversión
            $this->enviarNotificacionReversion($pedido, $motivo, $tipoReversion);

            // Broadcast evento
            $eventoTipo = match($tipoReversion) {
                'cancelacion' => 'pedido_cancelado',
                'cambio' => 'pedido_en_revision',
                'devolucion' => 'pedido_devuelto',
                default => 'pedido_revertido'
            };

            broadcast(new PedidoActualizado($pedido, $eventoTipo));
        });
    }

    /**
     * Crear registro de pago en caja cuando se confirma el pago de un pedido
     * Integración completa: crea tanto PedidoPago como Pago en caja
     */
    private function crearPagoDesdePedido(Pedido $pedido)
    {
        try {
            // Obtener la caja abierta actual
            $cajaAbierta = \App\Models\Caja::obtenerCajaAbierta(
                $pedido->empresaId,
                $pedido->sucursal_id ?? 1,
                now()->toDateString()
            );

            if (!$cajaAbierta) {
                // Si no hay caja abierta, crear una automáticamente
                $cajaAbierta = \App\Models\Caja::crearCajaDiaria(
                    $pedido->empresaId,
                    $pedido->sucursal_id ?? 1,
                    0,
                    'Caja automática para pedido #' . $pedido->id,
                    auth()->id() ?? $pedido->userId
                );
            }

                        // 2. Crear Pago en caja para el sistema contable
            $this->crearPagoEnCaja($pedido);

        } catch (\Exception $e) {
            // Si hay algún error al crear el pago, registrar en el log
            \Log::error('Error al crear pago desde pedido: ' . $e->getMessage(), [
                'pedido_id' => $pedido->id,
                'pedido_codigo' => $pedido->codigo
            ]);
        }
    }

    /**
     * Crear pago en el sistema de cajas (contabilidad)
     */
    private function crearPagoEnCaja(Pedido $pedido)
    {
        // Obtener o crear caja abierta
        $caja = \App\Models\Caja::obtenerCajaAbierta(
            $pedido->empresaId,
            $pedido->sucursal_id ?? 1, // Asumiendo sucursal 1 por defecto
            now()->toDateString()
        );

        if (!$caja) {
            // Si no hay caja abierta, crear una
            $caja = \App\Models\Caja::crearCajaDiaria(
                $pedido->empresaId,
                $pedido->sucursal_id ?? 1,
                0, // monto inicial
                'Caja creada automáticamente para pago de pedido',
                auth()->id() ?? $pedido->userId
            );
        }

        // Obtener concepto de pago para pedidos
        //$conceptoPago = $this->obtenerConceptoPagoPedidos();

        $pago = \App\Models\PedidoPago::where('pedidoId', $pedido->id)->update([
            'caja_id' => $caja->id,
            'status'  => 'Pagado'
        ]);

        // Actualizar totales de la caja
        $caja->calcularTotales();
    }

    /**
     * Obtener o crear concepto de pago para pedidos
     */
    private function obtenerConceptoPagoPedidos()
    {
        $concepto = \App\Models\ConceptoPago::where('nombre', 'Pedidos')
            ->where('empresa_id', auth()->user()->empresa_id ?? 1)
            ->where('sucursal_id', auth()->user()->sucursal_id ?? 1)
            ->first();

        if (!$concepto) {
            $concepto = \App\Models\ConceptoPago::create([
                'nombre' => 'Pedidos',
                'descripcion' => 'Ventas de pedidos de productos',
                'activo' => true,
                'empresa_id' => auth()->user()->empresa_id ?? 1,
                'sucursal_id' => auth()->user()->sucursal_id ?? 1
            ]);
        }

        return $concepto;
    }

    /**
     * Calcular total en bolívares basado en la tasa de cambio actual
     */
    private function calcularTotalBolivares($totalUsd)
    {
        $tasaCambio = $this->obtenerTasaCambioActual();
        return $tasaCambio ? $totalUsd * $tasaCambio : 0;
    }

    /**
     * Revertir el pago en caja cuando se revierte un pedido
     */
    private function revertirPagoEnCaja(Pedido $pedido, $motivo = null, $tipoReversion = 'cambio')
    {
        try {
            // Buscar el PedidoPago asociado al pedido
            $pedidoPago = PedidoPago::where('pedidoId', $pedido->id)->first();

            if ($pedidoPago) {
                // Si hay una caja asociada, recalcular sus totales
                if ($pedidoPago->caja) {
                    $pedidoPago->caja->calcularTotales();
                }

                // TODO: Si PedidoPago tuviera un campo estado, marcarlo como cancelado
                // Por ahora, podríamos agregar una nota o simplemente dejar el registro como está
                // ya que el pedido revertido ya indica que el pago fue revertido
            }

            // Buscar también en el sistema de caja escolar (si existe)
            if (class_exists('App\Models\Pago')) {
                $pago = \App\Models\Pago::where('referencia', $pedido->codigo)
                    ->where('empresa_id', $pedido->empresaId)
                    ->where('estado', \App\Models\Pago::ESTADO_APROBADO)
                    ->first();

                if ($pago) {
                    // Cancelar el pago
                    $pago->update([
                        'estado' => \App\Models\Pago::ESTADO_CANCELADO,
                        'observaciones' => ($pago->observaciones ? $pago->observaciones . "\n" : '') .
                            "Reversión de pedido - {$tipoReversion}: " . ($motivo ?? 'Sin motivo especificado')
                    ]);

                    // Actualizar totales de la caja
                    if ($pago->caja) {
                        $pago->caja->calcularTotales();
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error al revertir pago en caja: ' . $e->getMessage(), [
                'pedido_id' => $pedido->id,
                'pedido_codigo' => $pedido->codigo
            ]);
        }
    }

    /**
     * Obtener tasa de cambio actual
     */
    private function obtenerTasaCambioActual()
    {
        $tasa = \App\Models\ExchangeRate::where('fecha', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->first();

        return $tasa ? $tasa->tasa : null;
    }

    /**
     * Obtener el método de pago desde el pedido
     */
    private function obtenerMetodoPagoDesdePedido(Pedido $pedido)
    {
        // Buscar el método de pago en los detalles del pedido o en la información del cliente
        // Por defecto usamos 'efectivo' si no se encuentra información específica

        if ($pedido->metodo_pago) {
            return $pedido->metodo_pago;
        }

        // Si el pedido tiene información de pago en algún campo adicional
        if ($pedido->payment_method) {
            return $pedido->payment_method;
        }

        // Buscar en los detalles si hay información de método de pago
        $detallesPago = $pedido->detalles()->where('tipo', 'pago')->first();
        if ($detallesPago && $detallesPago->metodo_pago) {
            return $detallesPago->metodo_pago;
        }

        // Por defecto
        return 'efectivo';
    }

    /**
     * Devolver productos al almacén cuando se revierte un pedido
     */
    private function devolverProductosAlmacen(Pedido $pedido)
    {
        $detalles = $pedido->detalles()->with('producto')->get();

        foreach ($detalles as $detalle) {
            $producto = $detalle->producto;
            if ($producto) {
                // Validar que la cantidad sea un valor numérico válido
                $cantidad = $detalle->cantidad;

                // Manejar caso especial de cantidad NULL
                if (is_null($cantidad)) {
                    \Log::error("Cantidad NULL encontrada en detalle ID: {$detalle->id}. Este detalle será ignorado para evitar errores.");
                    continue;
                }

                if (!is_numeric($cantidad) || $cantidad <= 0) {
                    \Log::warning("Cantidad inválida para detalle ID: {$detalle->id}, cantidad: " . var_export($cantidad, true));
                    continue;
                }

                // Aumentar el stock del producto
                $producto->increment('quantity', $cantidad);

                // Registrar en histórico el movimiento de inventario
                $this->registrarHistorico($pedido, "Producto devuelto al almacén: {$producto->name} (Cant: {$cantidad})");
            }
        }
    }

    /**
     * Registrar evento en el histórico del pedido
     */
    private function registrarHistorico(Pedido $pedido, string $descripcion)
    {
        // Determinar un título apropiado basado en la descripción
        $titulo = match(true) {
            str_contains($descripcion, 'Pago confirmado') => 'Pago Confirmado',
            str_contains($descripcion, 'Empleado asignado') => 'Empleado Asignado',
            str_contains($descripcion, 'Pedido cancelado') => 'Pedido Cancelado',
            str_contains($descripcion, 'Pedido revertido') => 'Pedido Revertido',
            str_contains($descripcion, 'Entrega finalizada') => 'Entrega Finalizada',
            str_contains($descripcion, 'Producto devuelto') => 'Devolución de Producto',
            default => 'Actualización de Pedido'
        };

        HistoricoPedido::create([
            'fecha' => now()->format('Y-m-d'),
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'empresaId' => $pedido->empresaId,
            'userId' => auth()->id() ?? $pedido->userId, // Usar usuario autenticado o el del pedido
            'pedidoId' => $pedido->id
        ]);
    }

    private function enviarConfirmacionPago(Pedido $pedido)
    {
        $cliente = $pedido->user;
        if (!$cliente->telefono) return;

        $message = "¡Hola {$cliente->name}! 🎉\n\n";
        $message .= "Tu pago ha sido confirmado exitosamente.\n\n";
        $message .= "📋 **Detalles del pedido:**\n";
        $message .= "Código: {$pedido->codigo}\n";
        $message .= "Total: \${$pedido->total_usd}\n";

        // Obtener detalle mejorado del método de pago para el cliente
        $detallePago = $this->obtenerDetalleMetodoPagoCliente($pedido);
        $message .= $detallePago;

        $message .= "\nEn breves momentos nuestro empleado asignado se contactará contigo para coordinar la entrega.\n\n";
        $message .= "¡Gracias por tu compra! 🚀";

        $this->whatsappService->sendMessage($cliente->telefono, $message);
    }

    private function enviarAsignacionEmpleado(Pedido $pedido, Empleado $empleado)
    {
        if (!$empleado->telefono) return;

        $cliente = $pedido->user;
        $detalles = $pedido->detalles()->with('producto')->get();

        // Obtener detalle completo del método de pago
        $metodoPagoDetalle = $this->obtenerDetalleMetodoPagoCliente ($pedido);

        $message = "¡Hola {$empleado->nombres}! 📦\n\n";
        $message .= "Se te ha asignado un nuevo pedido:\n\n";
        $message .= "📋 **Información del pedido:**\n";
        $message .= "Código: {$pedido->codigo}\n";
        $message .= "Cliente: {$cliente->name}\n";
        $message .= "Teléfono: {$cliente->telefono}\n";
        $message .= "Total: \${$pedido->total_usd}\n";
        $message .= "Método de pago: {$metodoPagoDetalle}\n\n";

        $message .= "🛍️ **Productos:**\n";
        foreach ($detalles as $detalle) {
            $message .= "• {$detalle->producto->name} (Cant: {$detalle->quantity})\n";
        }

        if ($pedido->ubicacion) {
            $message .= "\n📍 **Ubicación:** {$pedido->ubicacion}\n";
            $message .= "\n📍 **Ubicación:** {$pedido->ubicacion}\n";
        }

        $message .= "\n⚠️ **Recordatorio:** No olvides marcar como entregado una vez completada la entrega.\n\n";
        $message .= "¡Éxito en tu entrega! 🚀";

        $this->whatsappService->sendMessage($empleado->telefono, $message);
    }

    /**
     * Enviar notificación al cliente sobre la asignación del empleado
     */
    private function enviarAsignacionCliente(Pedido $pedido, Empleado $empleado)
    {
        $cliente = $pedido->user;
        if (!$cliente->telefono) return;

        $message = "¡Hola {$cliente->name}! 📦\n\n";
        $message .= "Tu pedido ha sido asignado a uno de nuestros empleados para su entrega.\n\n";

        $message .= "👨‍💼 **Empleado asignado:**\n";
        $message .= "Nombre: {$empleado->nombres}\n";
        if ($empleado->telefono) {
            $message .= "Teléfono: {$empleado->telefono}\n";
        }

        $message .= "\n📋 **Información de tu pedido:**\n";
        $message .= "Código: {$pedido->codigo}\n";
        $message .= "Total: \${$pedido->total_usd}\n";

        // Obtener detalle del método de pago
        $detallePago = $this->obtenerDetalleMetodoPagoCliente($pedido);
        $message .= $detallePago;

        if ($pedido->ubicacion) {
            $message .= "\n📍 **Dirección de entrega:** {$pedido->ubicacion}\n";
        }

        $message .= "\n📞 El empleado se contactará contigo para coordinar la entrega.\n";
        $message .= "\n¡Gracias por tu compra! 🚀";

        $this->whatsappService->sendMessage($cliente->telefono, $message);
    }

    /**
     * Obtener el detalle completo del método de pago
     */
    private function obtenerDetalleMetodoPago($pedido)
    {
        $metodoBase = $pedido->metodo_pago;

        // Si hay pagos asociados (pago mixto)
        if ($pedido->pagos && $pedido->pagos->count() > 0) {
            $detalles = [];
            foreach ($pedido->pagos as $pago) {
                $monto = format_money($pago->total_usd);
                if ($pago->total_bs > 0) {
                    $monto .= " / " . format_money($pago->total_bs, 2) . "Bs";
                }

                $detallePago = "{$pago->metodo_pago}: {$monto}";

                // Agregar referencia si existe
                if ($pago->referencia_bancaria) {
                    $detallePago .= " (Ref: {$pago->referencia_bancaria})";
                }

                $detalles[] = $detallePago;
            }

            return "Mixto (" . implode(', ', $detalles) . ")";
        }

        // Si hay referencia bancaria
        if ($pedido->referenciaBancaria) {
            $ref = $pedido->referenciaBancaria;
            $detalle = $metodoBase;

            if ($ref->referencia) {
                $detalle .= " (Ref: {$ref->referencia})";
            }

            if ($ref->monto_efectivo > 0 || $ref->monto_restante > 0) {
                $partes = [];
                if ($ref->monto_efectivo > 0) {
                    $partes[] = "Efectivo: " . money($ref->monto_efectivo);
                }
                if ($ref->monto_restante > 0) {
                    $partes[] = "Restante: " . money($ref->monto_restante);
                }
                $detalle .= " [" . implode(' + ', $partes) . "]";
            }

            return $detalle;
        }

        // Método de pago simple
        return $metodoBase;
    }

    /**
     * Obtener el detalle del método de pago formateado para el cliente (estilo numerado)
     */
    private function obtenerDetalleMetodoPagoCliente($pedido)
    {
        $metodoBase = $pedido->metodo_pago;

        // Si hay pagos asociados (pago mixto)
        if ($pedido->pagos && $pedido->pagos->count() > 0) {
            $detalles = [];
            $contador = 1;

            foreach ($pedido->pagos as $pago) {
                $monto = format_money($pago->total_usd);
                if ($pago->total_bs > 0) {
                    $monto .= " / " . format_money($pago->total_bs, 2) . "Bs";
                }

                $detallePago = "{$contador}. {$pago->metodo_pago}: {$monto}";

                // Agregar referencia si existe
                if ($pago->referencia_bancaria) {
                    $detallePago .= " (Ref: {$pago->referencia_bancaria})";
                }

                $detalles[] = $detallePago;
                $contador++;
            }

            return "💳 **Método de pago:**\n" . implode("\n", $detalles);
        }

        // Si hay referencia bancaria con montos divididos
        if ($pedido->referenciaBancaria) {
            $ref = $pedido->referenciaBancaria;
            $detalles = [];
            $contador = 1;

            // Si hay monto en efectivo
            if ($ref->monto_efectivo > 0) {
                $detalles[] = "{$contador}. Efectivo: " . money($ref->monto_efectivo);
                $contador++;
            }

            // El método principal (transferencia, pago móvil, etc.)
            $metodoPrincipal = $metodoBase;
            if ($ref->referencia) {
                $metodoPrincipal .= " (Ref: {$ref->referencia})";
            }

            $montoRestante = $ref->monto_restante ?? 0;
            if ($montoRestante > 0) {
                $metodoPrincipal .= ": " . money($montoRestante);
            }

            $detalles[] = "{$contador}. {$metodoPrincipal}";

            return "💳 **Método de pago:**\n" . implode("\n", $detalles);
        }

        // Método de pago simple
        return "💳 **Método de pago:** {$metodoBase}";
    }

    private function enviarCancelacionPedido(Pedido $pedido, $motivo = null)
    {
        $cliente = $pedido->user;
        if (!$cliente->telefono) return;

        $message = "Hola {$cliente->name} 😔\n\n";
        $message .= "Lamentamos informarte que tu pedido ha sido cancelado.\n\n";
        $message .= "📋 **Detalles del pedido:**\n";
        $message .= "Código: {$pedido->codigo}\n";
        $message .= "Total: \${$pedido->total_usd}\n\n";

        if ($motivo) {
            $message .= "**Motivo:** {$motivo}\n\n";
        }

        $message .= "Si tienes alguna pregunta, no dudes en contactarnos.\n\n";
        $message .= "Disculpas por las molestias ocasionadas.";

        $this->whatsappService->sendMessage($cliente->telefono, $message);
    }

    private function enviarConfirmacionEntrega(Pedido $pedido)
    {
        $cliente = $pedido->user;
        if (!$cliente->telefono) return;

        $message = "¡Hola {$cliente->name}! ✅\n\n";
        $message .= "Tu pedido ha sido entregado exitosamente.\n\n";
        $message .= "📋 **Pedido:** {$pedido->codigo}\n";
        $message .= "💰 **Total:** \${$pedido->total_usd}\n\n";
        $message .= "¡Gracias por elegirnos! Esperamos verte pronto. 🎉";

        $this->whatsappService->sendMessage($cliente->telefono, $message);
    }

    /**
     * Enviar notificación de reversión a cliente y empleados
     */
    private function enviarNotificacionReversion(Pedido $pedido, $motivo, $tipoReversion)
    {
        // Notificar al cliente
        $this->enviarNotificacionReversionCliente($pedido, $motivo, $tipoReversion);

        // Notificar a empleados asignados
        $this->enviarNotificacionReversionEmpleados($pedido, $motivo, $tipoReversion);
    }

    /**
     * Enviar notificación de reversión al cliente
     */
    private function enviarNotificacionReversionCliente(Pedido $pedido, $motivo, $tipoReversion)
    {
        $cliente = $pedido->user;
        if (!$cliente->telefono) return;

        $titulo = match($tipoReversion) {
            'cancelacion' => '❌ Pedido Cancelado',
            'cambio' => '🔁 Solicitud de Cambio',
            'devolucion' => '🔄 Devolución de Pedido',
            default => '📋 Pedido en Revisión'
        };

        $mensajeInicial = match($tipoReversion) {
            'cancelacion' => 'Tu pedido ha sido cancelado',
            'cambio' => 'Has solicitado un cambio en tu pedido',
            'devolucion' => 'Has solicitado la devolución de tu pedido',
            default => 'Tu pedido está en revisión'
        };

        $message = "¡Hola {$cliente->name}! {$titulo}\n\n";
        $message .= "{$mensajeInicial}.\n\n";

        $message .= "📋 **Información del pedido:**\n";
        $message .= "Código: {$pedido->codigo}\n";
        $message .= "Total: \${$pedido->total_usd}\n";

        // Obtener detalle del método de pago
        $detallePago = $this->obtenerDetalleMetodoPagoCliente($pedido);
        $message .= $detallePago;

        if ($motivo) {
            $message .= "\n📝 **Motivo:** {$motivo}\n";
        }

        $message .= "\n⏰ **Fecha de solicitud:** " . now()->format('d/m/Y H:i') . "\n";

        $mensajeFinal = match($tipoReversion) {
            'cancelacion' => 'Procesaremos tu cancelación y te notificaremos sobre el reembolso.',
            'cambio' => 'Nuestro equipo revisará tu solicitud y se contactará contigo para coordinar los cambios.',
            'devolucion' => 'Procesaremos tu devolución y te notificaremos sobre los pasos a seguir.',
            default => 'Nuestro equipo revisará tu solicitud y se contactará contigo.'
        };

        $message .= "\n{$mensajeFinal}\n\n";
        $message .= "¡Gracias por tu comprensión! 🙏";

        $this->whatsappService->sendMessage($cliente->telefono, $message);
    }

    /**
     * Enviar notificación de reversión a empleados asignados
     */
    private function enviarNotificacionReversionEmpleados(Pedido $pedido, $motivo, $tipoReversion)
    {
        $empleados = $pedido->empleados;
        if ($empleados->isEmpty()) return;

        $titulo = match($tipoReversion) {
            'cancelacion' => '⚠️ Pedido Cancelado',
            'cambio' => '🔁 Solicitud de Cambio',
            'devolucion' => '🔄 Devolución de Pedido',
            default => '📋 Pedido en Revisión'
        };

        $tipoAccion = match($tipoReversion) {
            'cancelacion' => 'cancelado',
            'cambio' => 'en proceso de cambio',
            'devolucion' => 'en proceso de devolución',
            default => 'en revisión'
        };

        foreach ($empleados as $empleado) {
            if (!$empleado->telefono) continue;

            $message = "¡Hola {$empleado->nombres}! {$titulo}\n\n";
            $message .= "El siguiente pedido está {$tipoAccion}:\n\n";

            $message .= "📋 **Detalles del pedido:**\n";
            $message .= "Código: {$pedido->codigo}\n";
            $message .= "Cliente: {$pedido->user->name}\n";
            $message .= "Teléfono: {$pedido->user->telefono}\n";
            $message .= "Total: \${$pedido->total_usd}\n";

            if ($motivo) {
                $message .= "\n📝 **Motivo del cliente:** {$motivo}\n";
            }

            $mensajeAccion = match($tipoReversion) {
                'cancelacion' => 'Por favor, detén cualquier entrega pendiente y notifica a administración.',
                'cambio' => 'Por favor, contacta al cliente para coordinar los cambios solicitados.',
                'devolucion' => 'Por favor, contacta al cliente para coordinar la devolución.',
                default => 'Por favor, contacta al cliente para más detalles.'
            };

            $message .= "\n{$mensajeAccion}\n\n";
            $message .= "Fecha de solicitud: " . now()->format('d/m/Y H:i');

            $this->whatsappService->sendMessage($empleado->telefono, $message);
        }
    }
}
