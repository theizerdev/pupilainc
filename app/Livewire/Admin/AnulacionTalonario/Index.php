<?php

namespace App\Livewire\Admin\AnulacionTalonario;

use App\Traits\HasDynamicLayout;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AnulacionTalonario;
use App\Models\Serie;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $estado = '';
    public $tipo_documento = '';

    public $showModal = false;
    public $editingId = null;

    // Form properties
    public $serie_id = '';
    public $form_tipo_documento = '';
    public $serie_afectada = '';
    public $numero_control_desde = '';
    public $numero_control_hasta = '';
    public $correlativo_desde = '';
    public $correlativo_hasta = '';
    public $cantidad_documentos = 0;
    public $fecha_anulacion = '';
    public $motivo = '';
    public $descripcion_motivo = '';
    public $acta_destruccion = false;
    public $observaciones = '';
    public $fecha_reporte_seniat = '';
    public $numero_reporte_seniat = '';

    public function mount()
    {
        $this->fecha_anulacion = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function edit($id)
    {
        $record = AnulacionTalonario::findOrFail($id);

        $this->editingId = $record->id;
        $this->serie_id = $record->serie_id;
        $this->form_tipo_documento = $record->tipo_documento;
        $this->serie_afectada = $record->serie_afectada;
        $this->numero_control_desde = $record->numero_control_desde;
        $this->numero_control_hasta = $record->numero_control_hasta;
        $this->correlativo_desde = $record->correlativo_desde;
        $this->correlativo_hasta = $record->correlativo_hasta;
        $this->cantidad_documentos = $record->cantidad_documentos;
        $this->fecha_anulacion = $record->fecha_anulacion;
        $this->motivo = $record->motivo;
        $this->descripcion_motivo = $record->descripcion_motivo;
        $this->acta_destruccion = $record->acta_destruccion;
        $this->observaciones = $record->observaciones;
        $this->fecha_reporte_seniat = $record->fecha_reporte_seniat;
        $this->numero_reporte_seniat = $record->numero_reporte_seniat;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form_tipo_documento' => 'required',
            'serie_afectada' => 'required',
            'numero_control_desde' => 'required',
            'numero_control_hasta' => 'required',
            'correlativo_desde' => 'required',
            'correlativo_hasta' => 'required',
            'fecha_anulacion' => 'required|date',
            'motivo' => 'required',
            'descripcion_motivo' => 'required|min:10',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $sucursalId = auth()->user()->sucursal_id;

        // Validar que el rango no se solape con anulaciones existentes
        if (AnulacionTalonario::tieneRangoSolapado(
            $empresaId,
            $this->form_tipo_documento,
            $this->serie_afectada,
            $this->numero_control_desde,
            $this->numero_control_hasta,
            $this->editingId
        )) {
            $this->addError('numero_control_desde', 'El rango de números de control se solapa con una anulación ya registrada.');
            return;
        }

        // Advertencia de documentos ya emitidos en el rango
        $enUso = AnulacionTalonario::documentosEnUso(
            $empresaId,
            $this->form_tipo_documento,
            $this->serie_afectada,
            $this->numero_control_desde,
            $this->numero_control_hasta
        );

        $this->cantidad_documentos = abs($this->correlativo_hasta - $this->correlativo_desde) + 1;

        $data = [
            'serie_id' => $this->serie_id ?: null,
            'tipo_documento' => $this->form_tipo_documento,
            'serie_afectada' => $this->serie_afectada,
            'numero_control_desde' => $this->numero_control_desde,
            'numero_control_hasta' => $this->numero_control_hasta,
            'correlativo_desde' => $this->correlativo_desde,
            'correlativo_hasta' => $this->correlativo_hasta,
            'cantidad_documentos' => $this->cantidad_documentos,
            'fecha_anulacion' => $this->fecha_anulacion,
            'motivo' => $this->motivo,
            'descripcion_motivo' => $this->descripcion_motivo,
            'acta_destruccion' => $this->acta_destruccion,
            'observaciones' => $this->observaciones,
            'fecha_reporte_seniat' => $this->fecha_reporte_seniat ?: null,
            'numero_reporte_seniat' => $this->numero_reporte_seniat ?: null,
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            'user_id' => auth()->id(),
        ];

        if ($this->editingId) {
            AnulacionTalonario::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Anulación de talonario actualizada correctamente');
        } else {
            AnulacionTalonario::create($data);
            session()->flash('message', 'Anulación de talonario registrada correctamente');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($id)
    {
        AnulacionTalonario::findOrFail($id)->delete();
        session()->flash('message', 'Anulación de talonario eliminada correctamente');
    }

    public function marcarReportado($id)
    {
        $record = AnulacionTalonario::findOrFail($id);
        $record->update([
            'estado' => 'reportado_seniat',
            'fecha_reporte_seniat' => now(),
        ]);

        session()->flash('message', 'Anulación marcada como reportada al SENIAT');
    }

    public function marcarConfirmado($id)
    {
        $record = AnulacionTalonario::findOrFail($id);
        $record->update([
            'estado' => 'confirmado',
        ]);

        session()->flash('message', 'Anulación confirmada correctamente');
    }

    public function render()
    {
        $anulaciones = AnulacionTalonario::query()
            ->when($this->search, fn($q) => $q->where('serie_afectada', 'like', "%{$this->search}%")
                ->orWhere('numero_control_desde', 'like', "%{$this->search}%")
                ->orWhere('numero_control_hasta', 'like', "%{$this->search}%")
                ->orWhere('descripcion_motivo', 'like', "%{$this->search}%"))
            ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->when($this->tipo_documento, fn($q) => $q->where('tipo_documento', $this->tipo_documento))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $motivos = AnulacionTalonario::getMotivos();
        $estados = AnulacionTalonario::getEstados();

        $series = Serie::where('empresa_id', auth()->user()->empresa_id)->get();
        $tiposDocumento = Serie::getTiposDocumento();

        return $this->renderWithLayout('livewire.admin.anulacion-talonario.index', [
            'anulaciones' => $anulaciones,
            'motivos' => $motivos,
            'estados' => $estados,
            'series' => $series,
            'tiposDocumento' => $tiposDocumento,
        ], [
            'description' => 'Registro de Anulación de Talonarios',
        ]);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->serie_id = '';
        $this->form_tipo_documento = '';
        $this->serie_afectada = '';
        $this->numero_control_desde = '';
        $this->numero_control_hasta = '';
        $this->correlativo_desde = '';
        $this->correlativo_hasta = '';
        $this->cantidad_documentos = 0;
        $this->fecha_anulacion = now()->format('Y-m-d');
        $this->motivo = '';
        $this->descripcion_motivo = '';
        $this->acta_destruccion = false;
        $this->observaciones = '';
        $this->fecha_reporte_seniat = '';
        $this->numero_reporte_seniat = '';
    }
}
