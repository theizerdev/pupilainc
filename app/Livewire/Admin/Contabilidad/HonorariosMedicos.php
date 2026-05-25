<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ConsultaHonorario;
use App\Models\Medico;
use App\Models\Especialidad;

class HonorariosMedicos extends Component
{
    use HasDynamicLayout, WithPagination;

    public $fecha_desde;
    public $fecha_hasta;
    public $medico_id = '';
    public $especialidad_id = '';
    public $estado = '';
    public $mostrar_resumen = true;

    protected $queryString = [
        'medico_id' => ['except' => ''],
        'especialidad_id' => ['except' => ''],
        'estado' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function updatingMedicoId()
    {
        $this->resetPage();
    }

    public function updatingEspecialidadId()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['medico_id', 'especialidad_id', 'estado']);
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function getHonorariosProperty()
    {
        return ConsultaHonorario::with(['consulta.medico', 'consulta.especialidad', 'pago'])
            ->whereHas('consulta', function($q) {
                $q->whereBetween('fecha_consulta', [$this->fecha_desde, $this->fecha_hasta]);
                
                if ($this->medico_id) {
                    $q->where('medico_id', $this->medico_id);
                }
                
                if ($this->especialidad_id) {
                    $q->where('especialidad_id', $this->especialidad_id);
                }
            })
            ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->orderBy('fecha_calculo', 'desc')
            ->paginate(15);
    }

    public function getResumenProperty()
    {
        $query = ConsultaHonorario::whereHas('consulta', function($q) {
            $q->whereBetween('fecha_consulta', [$this->fecha_desde, $this->fecha_hasta]);
            
            if ($this->medico_id) {
                $q->where('medico_id', $this->medico_id);
            }
            
            if ($this->especialidad_id) {
                $q->where('especialidad_id', $this->especialidad_id);
            }
        });

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        return [
            'total_consultas' => $query->count(),
            'total_facturado_usd' => $query->sum('total_facturado_usd'),
            'total_honorarios_medico_usd' => $query->sum('total_honorarios_medico_usd'),
            'total_ingresos_clinica_usd' => $query->sum('total_ingresos_clinica_usd'),
        ];
    }

    public function getResumenPorMedicoProperty()
    {
        return ConsultaHonorario::selectRaw('
                consultas.medico_id,
                COUNT(*) as total_consultas,
                SUM(consulta_honorarios.total_facturado_usd) as total_facturado,
                SUM(consulta_honorarios.total_honorarios_medico_usd) as total_honorarios,
                SUM(consulta_honorarios.total_ingresos_clinica_usd) as total_ingresos_clinica
            ')
            ->join('consultas', 'consulta_honorarios.consulta_id', '=', 'consultas.id')
            ->with('consulta.medico')
            ->whereBetween('consultas.fecha_consulta', [$this->fecha_desde, $this->fecha_hasta])
            ->when($this->medico_id, fn($q) => $q->where('consultas.medico_id', $this->medico_id))
            ->when($this->especialidad_id, fn($q) => $q->where('consultas.especialidad_id', $this->especialidad_id))
            ->when($this->estado, fn($q) => $q->where('consulta_honorarios.estado', $this->estado))
            ->groupBy('consultas.medico_id')
            ->orderByDesc('total_honorarios')
            ->get();
    }

    public function getMedicosProperty()
    {
        return Medico::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('nombres')
            ->get();
    }

    public function getEspecialidadesProperty()
    {
        return Especialidad::where('status', true)
            ->orderBy('nombre')
            ->get();
    }

    protected function getPageTitle(): string
    {
        return 'Honorarios Médicos';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.contabilidad.honorarios-medicos' => 'Honorarios Médicos'
        ];
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.honorarios-medicos', [
            'honorarios' => $this->honorarios,
            'resumen' => $this->resumen,
            'resumenPorMedico' => $this->resumenPorMedico,
            'medicos' => $this->medicos,
            'especialidades' => $this->especialidades,
        ])->layout($this->getLayout());
    }
}