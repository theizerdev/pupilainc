<?php

namespace App\Livewire\Admin\ClienteFiscal;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ClienteFiscal;
use App\Models\Paciente;

class GestionClientesFiscales extends Component
{
    use WithPagination;

    public $modal = false;
    public $cliente_id;
    public $tipo_documento = 'V';
    public $numero_documento;
    public $razon_social;
    public $nombre_comercial;
    public $direccion_fiscal;
    public $ciudad;
    public $estado;
    public $codigo_postal;
    public $telefono;
    public $email;
    public $paciente_id;
    public $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'tipo_documento' => 'required',
        'numero_documento' => 'required',
        'razon_social' => 'required',
        'direccion_fiscal' => 'required',
        'telefono' => 'nullable',
        'email' => 'nullable|email'
    ];

    public function crear()
    {
        $this->reset(['cliente_id', 'tipo_documento', 'numero_documento', 'razon_social', 'nombre_comercial', 'direccion_fiscal', 'ciudad', 'estado', 'codigo_postal', 'telefono', 'email', 'paciente_id']);
        $this->tipo_documento = 'V';
        $this->modal = true;
    }

    public function editar($id)
    {
        $cliente = ClienteFiscal::findOrFail($id);
        $this->cliente_id = $cliente->id;
        $this->tipo_documento = $cliente->tipo_documento;
        $this->numero_documento = $cliente->numero_documento;
        $this->razon_social = $cliente->razon_social;
        $this->nombre_comercial = $cliente->nombre_comercial;
        $this->direccion_fiscal = $cliente->direccion_fiscal;
        $this->ciudad = $cliente->ciudad;
        $this->estado = $cliente->estado;
        $this->codigo_postal = $cliente->codigo_postal;
        $this->telefono = $cliente->telefono;
        $this->email = $cliente->email;
        $this->paciente_id = $cliente->paciente_id;
        $this->modal = true;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'razon_social' => $this->razon_social,
            'nombre_comercial' => $this->nombre_comercial,
            'direccion_fiscal' => $this->direccion_fiscal,
            'ciudad' => $this->ciudad,
            'estado' => $this->estado,
            'codigo_postal' => $this->codigo_postal,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'paciente_id' => $this->paciente_id,
            'empresa_id' => auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->sucursal_id
        ];

        if ($this->cliente_id) {
            ClienteFiscal::find($this->cliente_id)->update($data);
            session()->flash('success', 'Cliente fiscal actualizado');
        } else {
            ClienteFiscal::create($data);
            session()->flash('success', 'Cliente fiscal creado');
        }

        $this->modal = false;
        $this->reset();
    }

    public function eliminar($id)
    {
        ClienteFiscal::find($id)->delete();
        session()->flash('success', 'Cliente fiscal eliminado');
    }

    public function render()
    {
        $clientes = ClienteFiscal::with('paciente')
            ->when($this->search, fn($q) => $q->where('razon_social', 'like', "%{$this->search}%")
                ->orWhere('numero_documento', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);

        $pacientes = Paciente::where('activo', true)->get();

        return view('livewire.admin.cliente-fiscal.gestion-clientes-fiscales', compact('clientes', 'pacientes'));
    }
}
