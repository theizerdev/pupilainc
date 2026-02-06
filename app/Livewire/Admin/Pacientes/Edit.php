<?php

namespace App\Livewire\Admin\Pacientes;

use App\Models\Paciente;
use App\Models\Empresa;
use App\Models\Sucursal;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Validation\Rule;
use App\Traits\HasDynamicLayout;

class Edit extends Component
{
    use HasDynamicLayout;

    public $paciente_id;

    public $nombres;
    public $apellidos;
    public $documento_identidad;
    public $telefono;
    public $email;
    public $direccion;
    public $nickname;
    public $fecha_nacimiento;
    public $status;

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

    public function mount($paciente)
    {
        $paciente = Paciente::find($paciente);

        if (!$paciente) {
            abort(404);
        }

        $this->paciente_id = $paciente->id;
        $this->nombres = $paciente->nombres;
        $this->apellidos = $paciente->apellidos;
        $this->documento_identidad = $paciente->documento_identidad;
        $this->telefono = $paciente->telefono;
        $this->email = $paciente->email;
        $this->direccion = $paciente->direccion;
        $this->nickname = $paciente->nickname;
        $this->fecha_nacimiento = $paciente->fecha_nacimiento;
        $this->status = $paciente->status;
        $this->empresa_id = $paciente->empresa_id;
        $this->sucursal_id = $paciente->sucursal_id;

        if ($paciente->tutor) {
            $this->tutor = [
                'nombres' => $paciente->tutor->nombres ?? '',
                'apellidos' => $paciente->tutor->apellidos ?? '',
                'documento_identidad' => $paciente->tutor->documento_identidad ?? '',
                'parentesco' => $paciente->tutor->parentesco ?? '',
                'edad' => $paciente->tutor->edad ?? '',
                'telefono' => $paciente->tutor->telefono ?? '',
            ];
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
            'documento_identidad' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pacientes', 'documento_identidad')->ignore($this->paciente_id),
            ],
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'nickname' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'status' => 'boolean',
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

    public function update()
    {
        $this->validate();

        $paciente = Paciente::findOrFail($this->paciente_id);

        $paciente->update([
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'documento_identidad' => $this->documento_identidad,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'nickname' => $this->nickname,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'status' => $this->status,
            'empresa_id' => $this->empresa_id,
            'sucursal_id' => $this->sucursal_id,
        ]);

        if ($this->isMinor()) {
            $paciente->tutor()->updateOrCreate(
                ['paciente_id' => $paciente->id],
                $this->tutor
            );
        } else {
            $paciente->tutor()->delete();
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Paciente '{$this->nombres} {$this->apellidos}' actualizado exitosamente.",
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
        return view('livewire.admin.pacientes.edit', [
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}