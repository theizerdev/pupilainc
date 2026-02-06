<?php

namespace App\Livewire\Admin\Series;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\Serie;
use App\Models\Empresa;
use App\Models\Sucursal;

class Create extends Component
{
    use HasDynamicLayout;


    public $tipo_documento = 'recibo';
    public $serie = '';
    public $correlativo_actual = 0;
    public $longitud_correlativo = 8;
    public $activo = true;
    public $empresa_id;
    public $sucursal_id;

    public $empresas = [];
    public $sucursales = [];

    protected $rules = [
        'tipo_documento' => 'required|in:factura,boleta,nota_credito,recibo',
        'serie' => 'required|string|max:10',
        'correlativo_actual' => 'required|integer|min:0',
        'longitud_correlativo' => 'required|integer|min:4|max:12',
        'empresa_id' => 'required|exists:empresas,id',
        'sucursal_id' => 'required|exists:sucursales,id'
    ];

    public function mount()
    {
        $this->cargarDatos();
        $this->generarSerieSugerida();
    }

    public function cargarDatos()
    {
        if (auth()->user()->hasRole('Super Administrador')) {
            $this->empresas = Empresa::all();
        } else {
            $this->empresa_id = auth()->user()->empresa_id;
            $this->sucursal_id = auth()->user()->sucursal_id;
        }

        if ($this->empresa_id) {
            $this->sucursales = Sucursal::where('empresa_id', $this->empresa_id)->get();
        }
    }

    public function updatedEmpresaId($value)
    {
        $this->sucursales = Sucursal::where('empresa_id', $value)->get();
        $this->sucursal_id = null;
    }

    public function updatedTipoDocumento()
    {
        $this->generarSerieSugerida();
    }

    public function generarSerieSugerida()
    {
        $prefijos = [
            'factura' => 'F',
            'boleta' => 'B',
            'nota_credito' => 'NC',
            'recibo' => 'R'
        ];

        $prefijo = $prefijos[$this->tipo_documento] ?? 'R';

        $ultimaSerie = Serie::where('tipo_documento', $this->tipo_documento)
            ->where('serie', 'like', "$prefijo%")
            ->orderBy('serie', 'desc')
            ->first();

        if ($ultimaSerie) {
            preg_match('/\d+/', $ultimaSerie->serie, $matches);
            $numero = isset($matches[0]) ? (int)$matches[0] + 1 : 1;
        } else {
            $numero = 1;
        }

        $this->serie = $prefijo . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    public function guardar()
    {
        $this->validate();

        $existe = Serie::where('serie', $this->serie)
            ->where('empresa_id', $this->empresa_id)
            ->where('sucursal_id', $this->sucursal_id)
            ->exists();

        if ($existe) {
            $this->addError('serie', 'Esta serie ya existe para la empresa/sucursal seleccionada');
            return;
        }

        Serie::create([
            'tipo_documento' => $this->tipo_documento,
            'serie' => $this->serie,
            'correlativo_actual' => $this->correlativo_actual,
            'longitud_correlativo' => $this->longitud_correlativo,
            'activo' => $this->activo,
            'empresa_id' => $this->empresa_id,
            'sucursal_id' => $this->sucursal_id
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Serie '{$this->serie}' creada exitosamente.",
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
        return view('livewire.admin.series.create',compact('tipos'))->layout($this->getLayout());
    }
}