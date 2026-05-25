<?php

namespace App\Livewire\Admin\Citas;

use App\Models\CitaRecordatorio;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Recordatorios extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $estado = '';
    public $tipo = '';
    public $canal = '';
    public $fechaDesde = '';
    public $fechaHasta = '';

    public $stats = [];

    public ?int $viendoId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'tipo' => ['except' => ''],
        'canal' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updated($name)
    {
        if (in_array($name, ['search', 'estado', 'tipo', 'canal', 'fechaDesde', 'fechaHasta'])) {
            $this->resetPage();
        }
    }

    public function mount()
    {
        $this->calcularStats();
    }

    public function calcularStats()
    {
        $empresaId = auth()->user()->empresa_id;
        
        $this->stats = [
            'total' => CitaRecordatorio::whereHas('cita', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })->count(),
            
            'pendientes' => CitaRecordatorio::whereHas('cita', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })->where('estado', 'pendiente')->count(),
            
            'enviados' => CitaRecordatorio::whereHas('cita', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })->where('estado', 'enviado')->count(),
            
            'fallidos' => CitaRecordatorio::whereHas('cita', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })->where('estado', 'fallido')->count(),
            
            'cancelados' => CitaRecordatorio::whereHas('cita', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })->where('estado', 'cancelado')->count(),
            
            'por_enviar' => CitaRecordatorio::whereHas('cita', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })->pendientes()->count(),
        ];
    }

    public function getHayFiltrosActivosProperty(): bool
    {
        return $this->search !== '' || $this->estado !== '' || $this->tipo !== '' || $this->canal !== '' || $this->fechaDesde !== '' || $this->fechaHasta !== '';
    }

    public function filtrarPorEstado(?string $estado = null)
    {
        $this->estado = $estado ?: '';
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'estado', 'tipo', 'canal', 'fechaDesde', 'fechaHasta']);
        $this->resetPage();
        $this->calcularStats();
    }

    public function render()
    {
        $recordatorios = CitaRecordatorio::with(['cita.paciente', 'cita.medico', 'cita.especialidad'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('cita.paciente', function ($sub) {
                        $sub->where('nombre', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido', 'like', '%' . $this->search . '%')
                            ->orWhere('cedula', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('cita.medico', function ($sub) {
                        $sub->where('nombre', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->estado, function ($query) {
                $query->where('estado', $this->estado);
            })
            ->when($this->tipo, function ($query) {
                $query->where('tipo', $this->tipo);
            })
            ->when($this->canal, function ($query) {
                $query->where('canal', $this->canal);
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha_envio_programado', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha_envio_programado', '<=', $this->fechaHasta);
            })
            ->whereHas('cita', function ($query) {
                $query->where('empresa_id', auth()->user()->empresa_id);
            })
            ->orderBy('fecha_envio_programado', 'desc')
            ->paginate(15);

        return view('livewire.admin.citas.recordatorios-materialize', [
            'recordatorios' => $recordatorios,
            'stats' => $this->stats,
            'estados' => [
                'pendiente' => 'Pendiente',
                'enviado' => 'Enviado',
                'fallido' => 'Fallido',
                'cancelado' => 'Cancelado',
            ],
            'tipos' => [
                '48h' => '48 Horas',
                '24h' => '24 Horas',
                '2h' => '2 Horas',
                'personalizado' => 'Personalizado',
            ],
            'canales' => [
                'whatsapp' => 'WhatsApp',
                'email' => 'Email',
                'sms' => 'SMS',
            ],
        ])->layout($this->getLayout());
    }

    public function verMensaje($recordatorioId)
    {
        $recordatorio = CitaRecordatorio::with('cita')->findOrFail($recordatorioId);
        if ($recordatorio->cita->empresa_id !== auth()->user()->empresa_id) {
            return;
        }
        $this->viendoId = $recordatorioId;
    }

    public function cerrarMensaje()
    {
        $this->viendoId = null;
    }

    public function reenviarRecordatorio($recordatorioId)
    {
        try {
            $recordatorio = CitaRecordatorio::findOrFail($recordatorioId);
            if ($recordatorio->cita->empresa_id !== auth()->user()->empresa_id) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'No tiene permisos para reenviar este recordatorio.', 'duration' => 4000]);
                return;
            }
            $recordatorio->update([
                'estado' => 'pendiente',
                'fecha_envio' => null,
                'intentos' => 0,
                'error_mensaje' => null,
            ]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Recordatorio reprogramado para envío.', 'duration' => 4000]);
            $this->calcularStats();
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al reenviar recordatorio: ' . $e->getMessage(), 'duration' => 5000]);
        }
    }

    public function cancelarRecordatorio($recordatorioId)
    {
        try {
            $recordatorio = CitaRecordatorio::findOrFail($recordatorioId);
            if ($recordatorio->cita->empresa_id !== auth()->user()->empresa_id) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'No tiene permisos para cancelar este recordatorio.', 'duration' => 4000]);
                return;
            }
            $recordatorio->update([
                'estado' => 'cancelado',
                'error_mensaje' => 'Cancelado manualmente',
            ]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Recordatorio cancelado exitosamente.', 'duration' => 4000]);
            $this->calcularStats();
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al cancelar recordatorio: ' . $e->getMessage(), 'duration' => 5000]);
        }
    }

    public function reenviarTodosFallidos()
    {
        $empresaId = auth()->user()->empresa_id;
        $count = CitaRecordatorio::where('estado', 'fallido')
            ->whereHas('cita', fn($q) => $q->where('empresa_id', $empresaId))
            ->update([
                'estado' => 'pendiente',
                'fecha_envio_real' => null,
                'intentos' => 0,
                'error_mensaje' => null,
            ]);
        $this->dispatch('notify', ['type' => 'success', 'message' => "Se reprogramaron {$count} recordatorios fallidos.", 'duration' => 4000]);
        $this->calcularStats();
    }

    public function cancelarTodosPendientes()
    {
        $empresaId = auth()->user()->empresa_id;
        $count = CitaRecordatorio::where('estado', 'pendiente')
            ->whereHas('cita', fn($q) => $q->where('empresa_id', $empresaId))
            ->update([
                'estado' => 'cancelado',
                'error_mensaje' => 'Cancelado masivamente',
            ]);
        $this->dispatch('notify', ['type' => 'success', 'message' => "Se cancelaron {$count} recordatorios pendientes.", 'duration' => 4000]);
        $this->calcularStats();
    }

    public function procesarPendientes()
    {
        try {
            $pendientes = CitaRecordatorio::pendientes()
                ->whereHas('cita', function ($query) {
                    $query->where('empresa_id', auth()->user()->empresa_id);
                })
                ->count();
            if ($pendientes === 0) {
                $this->dispatch('notify', ['type' => 'info', 'message' => 'No hay recordatorios pendientes para procesar.', 'duration' => 4000]);
                return;
            }
            \Artisan::call('citas:procesar-recordatorios');
            $this->dispatch('notify', ['type' => 'success', 'message' => "Procesados {$pendientes} recordatorios pendientes.", 'duration' => 4000]);
            $this->calcularStats();
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al procesar recordatorios: ' . $e->getMessage(), 'duration' => 5000]);
        }
    }
}
