<?php

namespace App\Livewire\Admin\Series;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\Serie;

class Edit extends Component
{
    use HasDynamicLayout;


    public Serie $serie;
    public $tipo_documento;
    public $serie_codigo;
    public $correlativo_actual;
    public $longitud_correlativo;
    public $activo;

    protected $rules = [
        'tipo_documento' => 'required|in:factura,boleta,nota_credito,recibo',
        'serie_codigo' => 'required|string|max:10',
        'correlativo_actual' => 'required|integer|min:0',
        'longitud_correlativo' => 'required|integer|min:4|max:12'
    ];

    public function mount(Serie $serie)
    {
        $this->serie = $serie;
        $this->tipo_documento = $serie->tipo_documento;
        $this->serie_codigo = $serie->serie;
        $this->correlativo_actual = $serie->correlativo_actual;
        $this->longitud_correlativo = $serie->longitud_correlativo;
        $this->activo = $serie->activo;
    }

    public function guardar()
    {
       

        $existe = Serie::where('serie', $this->serie_codigo)
            ->where('empresa_id', $this->serie->empresa_id)
            ->where('sucursal_id', $this->serie->sucursal_id)
            ->where('id', '!=', $this->serie->id)
            ->exists();

        if ($existe) {
            $this->addError('serie_codigo', 'Esta serie ya existe para la empresa/sucursal');
            return;
        }

        $this->serie->update([
            'tipo_documento' => $this->tipo_documento,
            'serie' => $this->serie_codigo,
            'correlativo_actual' => $this->correlativo_actual,
            'longitud_correlativo' => $this->longitud_correlativo,
            'activo' => $this->activo
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Serie '{$this->serie_codigo}' actualizada exitosamente.",
            'duration' => 4000
        ]);
        return redirect()->route('admin.series.index');
    }

    public function render()
    {
        $tipos = [
            'factura' => 'Factura',
            'boleta' => 'Boleta',
            'nota_credito' => 'Nota de Crédito',
            'recibo' => 'Recibo',
            'comunidad educativa' => 'Comunidad Educativa',
            'educacion adulto' => 'Educación de Adultos',
        ];
        return view('livewire.admin.series.edit',compact('tipos'))->layout($this->getLayout());
    }
}