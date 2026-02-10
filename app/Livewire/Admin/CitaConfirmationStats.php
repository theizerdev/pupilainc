<?php

namespace App\Livewire\Admin;

use App\Models\Cita;
use App\Models\CitaConfirmacion;
use App\Services\CitaConfirmationService;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class CitaConfirmationStats extends Component
{
    use WithPagination;

    public $filtroEstado = '';
    public $filtroFechaInicio;
    public $filtroFechaFin;
    public $filtroMetodo = '';
    
    public $stats = [];
    public $confirmacionesRecientes = [];
    
    // Propiedades para envío manual de confirmaciones
    public $citasDisponibles = [];
    public $citaSeleccionada = null;
    public $mostrarModalEnvio = false;
    
    protected $listeners = ['refreshStats' => 'cargarEstadisticas'];


    public function mount()
    {
        $this->filtroFechaInicio = now()->subDays(30)->format('Y-m-d');
        $this->filtroFechaFin = now()->format('Y-m-d');
        $this->cargarEstadisticas();
    }

    public function cargarEstadisticas()
    {
        $query = CitaConfirmacion::query()
            ->whereBetween('created_at', [
                Carbon::parse($this->filtroFechaInicio)->startOfDay(),
                Carbon::parse($this->filtroFechaFin)->endOfDay()
            ]);

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroMetodo) {
            $query->where('metodo', $this->filtroMetodo);
        }

        // Aplicar filtros de empresa/sucursal según el usuario
        $query->forUser();

        $confirmaciones = $query->get();

        $this->stats = [
            'total_confirmaciones' => $confirmaciones->count(),
            'confirmadas' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_CONFIRMADO)->count(),
            'rechazadas' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_RECHAZADO)->count(),
            'pendientes' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_PENDIENTE)->count(),
            'sin_respuesta' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_SIN_RESPUESTA)->count(),
            'expiradas' => $confirmaciones->where('estado', CitaConfirmacion::ESTADO_EXPIRADO)->count(),
            'tasa_confirmacion' => $confirmaciones->count() > 0 
                ? round(($confirmaciones->where('estado', CitaConfirmacion::ESTADO_CONFIRMADO)->count() / $confirmaciones->count()) * 100, 2)
                : 0,
            'tasa_respuesta' => $confirmaciones->count() > 0
                ? round((($confirmaciones->where('estado', CitaConfirmacion::ESTADO_CONFIRMADO)->count() + 
                         $confirmaciones->where('estado', CitaConfirmacion::ESTADO_RECHAZADO)->count()) / $confirmaciones->count()) * 100, 2)
                : 0,
        ];

        // Cargar confirmaciones recientes
        $this->confirmacionesRecientes = CitaConfirmacion::with(['cita.paciente', 'cita.medico', 'empresa', 'sucursal'])
            ->forUser()
            ->latest()
            ->limit(10)
            ->get();
    }

    public function reintentarConfirmacion($confirmacionId)
    {
        try {
            $confirmacion = CitaConfirmacion::find($confirmacionId);
            
            if (!$confirmacion) {
                $this->dispatch('notify', type: 'error', message: 'Confirmación no encontrada');
                return;
            }

            if (!$confirmacion->puedeReintentar()) {
                $this->dispatch('notify', type: 'warning', message: 'Esta confirmación no puede reintentarse');
                return;
            }

            // Despachar job de reintento
            \App\Jobs\RetryFailedConfirmation::dispatch($confirmacionId);

            $this->dispatch('notify', type: 'success', message: 'Reintento programado correctamente');
            $this->cargarEstadisticas();

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al programar reintento: ' . $e->getMessage());
        }
    }

    public function reintentarPendientes()
    {
        try {
            $pendientes = CitaConfirmacion::pendientes()
                ->where('intentos', '<', 3)
                ->forUser()
                ->get();

            $contador = 0;
            foreach ($pendientes as $confirmacion) {
                \App\Jobs\RetryFailedConfirmation::dispatch($confirmacion->id);
                $contador++;
            }

            $this->dispatch('notify', type: 'success', message: "{$contador} confirmaciones programadas para reintento");
            $this->cargarEstadisticas();

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al programar reintentos: ' . $e->getMessage());
        }
    }

    public function updatedFiltroEstado()
    {
        $this->cargarEstadisticas();
    }

    public function updatedFiltroMetodo()
    {
        $this->cargarEstadisticas();
    }

    public function updatedFiltroFechaInicio()
    {
        $this->cargarEstadisticas();
    }

    public function updatedFiltroFechaFin()
    {
        $this->cargarEstadisticas();
    }

    public function render()
    {
        return view('livewire.admin.cita-confirmation-stats');
    }
    
    /**
     * Abre el modal para enviar confirmaciones manuales
     */
    public function abrirModalEnvio()
    {
        $this->mostrarModalEnvio = true;
        $this->fechaDesde = now()->format('Y-m-d');
        $this->fechaHasta = now()->addDays(7)->format('Y-m-d');
        $this->buscarCitasSinConfirmar();
    }
    
    /**
     * Cierra el modal de envío
     */
    public function cerrarModalEnvio()
    {
        $this->mostrarModalEnvio = false;
        $this->citasSinConfirmar = [];
        $this->citasSeleccionadas = [];
        $this->busquedaPaciente = '';
    }
    
    /**
     * Busca citas que necesitan confirmación
     */
    public function buscarCitasSinConfirmar()
    {
        try {
            $query = Cita::with(['paciente', 'medico', 'especialidad', 'sucursal'])
                ->where('estado', Cita::ESTADO_PENDIENTE)
                ->where('fecha_inicio', '>', now()->addHours(24))
                ->whereDoesntHave('confirmaciones', function($q) {
                    $q->whereIn('estado', [CitaConfirmacion::ESTADO_PENDIENTE, CitaConfirmacion::ESTADO_CONFIRMADO]);
                })
                ->whereBetween('fecha_inicio', [
                    Carbon::parse($this->fechaDesde)->startOfDay(),
                    Carbon::parse($this->fechaHasta)->endOfDay()
                ]);
            
            // Aplicar filtros de empresa/sucursal
            if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
                if (auth()->user()->empresa_id) {
                    $query->where('empresa_id', auth()->user()->empresa_id);
                }
                if (auth()->user()->sucursal_id) {
                    $query->where('sucursal_id', auth()->user()->sucursal_id);
                }
            }
            
            // Búsqueda por paciente
            if ($this->busquedaPaciente) {
                $query->whereHas('paciente', function($q) {
                    $q->where('nombre', 'like', '%' . $this->busquedaPaciente . '%')
                      ->orWhere('apellido', 'like', '%' . $this->busquedaPaciente . '%')
                      ->orWhere('telefono', 'like', '%' . $this->busquedaPaciente . '%');
                });
            }
            
            $this->citasSinConfirmar = $query->orderBy('fecha_inicio')->get();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error buscando citas: ' . $e->getMessage());
        }
    }
    
    /**
     * Selecciona o deselecciona todas las citas
     */
    public function seleccionarTodas()
    {
        if (count($this->citasSeleccionadas) === count($this->citasSinConfirmar)) {
            $this->citasSeleccionadas = [];
        } else {
            $this->citasSeleccionadas = $this->citasSinConfirmar->pluck('id')->toArray();
        }
    }
    
    /**
     * Envía confirmaciones a las citas seleccionadas
     */
    public function enviarConfirmacionesSeleccionadas()
    {
        try {
            if (empty($this->citasSeleccionadas)) {
                $this->dispatch('notify', type: 'warning', message: 'Seleccione al menos una cita');
                return;
            }
            
            $service = new CitaConfirmationService();
            $enviadas = 0;
            $fallidas = 0;
            
            foreach ($this->citasSeleccionadas as $citaId) {
                try {
                    $cita = Cita::find($citaId);
                    
                    if (!$cita || !$cita->necesitaConfirmacion()) {
                        continue;
                    }
                    
                    $confirmacion = $service->iniciarConfirmacion($cita);
                    
                    if ($confirmacion) {
                        $enviadas++;
                    } else {
                        $fallidas++;
                    }
                    
                    // Pequeña pausa entre envíos
                    usleep(500000); // 0.5 segundos
                    
                } catch (\Exception $e) {
                    Log::error("Error enviando confirmación para cita {$citaId}: " . $e->getMessage());
                    $fallidas++;
                }
            }
            
            $mensaje = "Envío completado: {$enviadas} exitosas";
            if ($fallidas > 0) {
                $mensaje .= ", {$fallidas} fallidas";
            }
            
            $this->dispatch('notify', type: 'success', message: $mensaje);
            $this->cerrarModalEnvio();
            $this->cargarEstadisticas();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error en envío: ' . $e->getMessage());
        }
    }
    
    /**
     * Envía confirmación para una cita específica (desde la tabla)
     */
    public function enviarConfirmacionCita($citaId)
    {
        try {
            $cita = Cita::find($citaId);
            
            if (!$cita) {
                $this->dispatch('notify', type: 'error', message: 'Cita no encontrada');
                return;
            }
            
            if (!$cita->necesitaConfirmacion()) {
                $this->dispatch('notify', type: 'warning', message: 'Esta cita no necesita confirmación');
                return;
            }
            
            $service = new CitaConfirmationService();
            $confirmacion = $service->iniciarConfirmacion($cita);
            
            if ($confirmacion) {
                $this->dispatch('notify', type: 'success', message: 'Confirmación enviada correctamente');
                $this->cargarEstadisticas();
            } else {
                $this->dispatch('notify', type: 'error', message: 'Error al enviar confirmación');
            }
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }
}
