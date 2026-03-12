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

    public $showTutorSection = false;

    protected $queryString = [
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => ''],
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

    public function updatedFechaNacimiento()
    {
        $this->validateOnly('fecha_nacimiento');
        $this->showTutorSection = $this->isMinor();
    }

    public function updatedTutor($value, $field)
    {
        $this->validateOnly("tutor.{$field}");
    }

    protected function rules()
    {
        $rules = [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:50|unique:pacientes,documento_identidad',
            'telefono' => 'nullable|regex:/^[\d\s\-\+\(\)]+$/|max:20',
            'email' => 'nullable|email|max:255|unique:pacientes,email',
            'direccion' => 'nullable|string|max:500',
            'nickname' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
        ];

        if (auth()->user()->hasRole('Super Administrador')) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        if ($this->showTutorSection) {
            $rules['tutor.nombres'] = 'required|string|max:255';
            $rules['tutor.apellidos'] => 'required|string|max:255';
            $rules['tutor.documento_identidad'] = 'nullable|string|max:50';
            $rules['tutor.parentesco'] = 'required|string|max:100';
            $rules['tutor.edad'] = 'nullable|integer|min:1|max:120';
            $rules['tutor.telefono'] = 'nullable|regex:/^[\d\s\-\+\(\)]+$/|max:20';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return [
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones, paréntesis y el signo +.',
            'tutor.telefono.regex' => 'El teléfono del tutor solo puede contener números, espacios, guiones, paréntesis y el signo +.',
        ];
    }

    public function isMinor(): bool
    {
        if (empty($this->fecha_nacimiento)) {
            return false;
        }

        try {
            $age = Carbon::parse($this->fecha_nacimiento)->age;
            return $age < 18;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getEdadAttribute(): ?string
    {
        if (empty($this->fecha_nacimiento)) {
            return null;
        }

        try {
            $years = Carbon::parse($this->fecha_nacimiento)->age;
            $months = Carbon::parse($this->fecha_nacimiento)->diffInMonths(now()) % 12;
            
            if ($years > 0) {
                return "{$years} año" . ($years > 1 ? 's' : '') . ($months > 0 ? " y {$months} mese" . ($months > 1 ? 's' : '') : '');
            } elseif ($months > 0) {
                return "{$months} mes" . ($months > 1 ? 'es' : '');
            } else {
                return 'Recién nacido';
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    public function formatDocumento()
    {
        if ($this->documento_identidad) {
            $this->documento_identidad = strtoupper(trim($this->documento_identidad));
        }
    }

    public function formatPhone()
    {
        if ($this->telefono) {
            $this->telefono = preg_replace('/[^0-9+]/', '', $this->telefono);
        }
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

        if ($this->showTutorSection && !empty(array_filter($this->tutor))) {
            $paciente->tutor()->create([
                'nombres' => $validated['tutor']['nombres'],
                'apellidos' => $validated['tutor']['apellidos'],
                'documento_identidad' => $validated['tutor']['documento_identidad'] ?? null,
                'parentesco' => $validated['tutor']['parentesco'],
                'edad' => $validated['tutor']['edad'] ?? null,
                'telefono' => $validated['tutor']['telefono'] ?? null,
            ]);
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
            'edad' => $this->edad,
        ])->layout($this->getLayout());
    }
}