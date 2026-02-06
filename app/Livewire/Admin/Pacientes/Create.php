<?php

namespace App\Livewire\Admin\Pacientes;

use App\Models\Paciente;
use App\Models\Empresa;
use App\Models\Sucursal;
use Carbon\Carbon;
use Livewire\Component;

use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use HasDynamicLayout;

    public $nombres;
    public $apellidos;
    public $documento_identidad;
    public $telefono;
    public $email;
    public $direccion;
    public $nickname;
    public $fecha_nacimiento;

    public $empresa_id;
    public $sucursal_id;

    public $pais;

    public $tutor = [
        'nombres' => '',
        'apellidos' => '',
        'documento_identidad' => '',
        'parentesco' => '',
        'edad' => '',
        'telefono' => '',
    ];

    public function mount()
    {
        $this->authorize('create pacientes');

        if (!auth()->user()->hasRole('Super Administrador')) {
            $this->empresa_id = auth()->user()->empresa_id;
            $this->sucursal_id = auth()->user()->sucursal_id;
        }

        if (auth()->user()->empresa) {
            $this->pais = auth()->user()->empresa->pais;
        }
    }

    protected function rules()
    {
        $rules = [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'nickname' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
        ];

        if (auth()->user()->hasRole('Super Administrador')) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        if ($this->isMinor()) {
            $rules['tutor.nombres'] = 'required|string|max:255';
            $rules['tutor.apellidos'] = 'required|string|max:255';
            $rules['tutor.documento_identidad'] = 'nullable|string|max:50';
            $rules['tutor.parentesco'] = 'required|string|max:100';
            $rules['tutor.edad'] = 'nullable|integer|min:1';
            $rules['tutor.telefono'] = 'nullable|string|max:20';
        }

        return $rules;
    }

    public function isMinor(): bool
    {
        if (empty($this->fecha_nacimiento)) {
            return false;
        }

        $age = Carbon::parse($this->fecha_nacimiento)->age;

        return $age < 18;
    }

    public function store()
    {
        $this->authorize('create pacientes');

        $validated = $this->validate();

        $paciente = Paciente::create([
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'documento_identidad' => $validated['documento_identidad'],
            'telefono' => $validated['telefono'] ?? null,
            'email' => $validated['email'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'nickname' => $validated['nickname'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
            'empresa_id' => auth()->user()->hasRole('Super Administrador') ? $validated['empresa_id'] : auth()->user()->empresa_id,
            'sucursal_id' => auth()->user()->hasRole('Super Administrador') ? $validated['sucursal_id'] : auth()->user()->sucursal_id,
        ]);

        if ($this->isMinor() && !empty(array_filter($this->tutor))) {
            $paciente->tutor()->create($this->tutor);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Paciente '{$validated['nombres']} {$validated['apellidos']}' creado exitosamente.",
            'duration' => 4000
        ]);

        return redirect()->route('admin.pacientes.index');
    }

    public function getEmpresasProperty()
    {
        return Empresa::orderBy('razon_social')->get();
    }

    public function getSucursalesProperty()
    {
        if ($this->empresa_id) {
            return Sucursal::where('empresa_id', $this->empresa_id)->orderBy('nombre')->get();
        }
        return collect();
    }

    public function render()
    {
        return view('livewire.admin.pacientes.create', [
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}