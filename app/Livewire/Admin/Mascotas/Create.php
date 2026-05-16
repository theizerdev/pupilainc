<?php

namespace App\Livewire\Admin\Mascotas;

use Livewire\Component;
use App\Models\Mascota;
use App\Models\Especie;
use App\Models\Raza;
use App\Models\Propietario;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use WithFileUploads;
    use HasDynamicLayout;

    // Datos de la mascota
    public $nombre;
    public $especie_id;
    public $raza_id;
    public $sexo = 'macho';
    public $fecha_nacimiento;
    public $peso_actual_kg;
    public $color_pelaje;
    public $marcas_distintivas;
    public $microchip;
    public $numero_registro;
    public $foto;
    public $esterilizado = false;
    public $fecha_esterilizacion;
    public $notas_generales;
    public $alergias_conocidas;
    public $condiciones_cronicas;
    public $nivel_agresividad = 'tranquilo';

    // Propietario
    public $propietario_id;
    public $showNuevoPropietario = false;
    public $prop_nombres;
    public $prop_apellidos;
    public $prop_documento_identidad;
    public $prop_telefono;
    public $prop_email;
    public $prop_direccion;

    // UI
    public $razas = [];

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'especie_id' => 'required|exists:especies,id',
        'raza_id' => 'nullable|exists:razas,id',
        'sexo' => 'required|in:macho,hembra',
        'fecha_nacimiento' => 'nullable|date|before_or_equal:today',
        'peso_actual_kg' => 'nullable|numeric|min:0.01|max:999.99',
        'color_pelaje' => 'nullable|string|max:255',
        'marcas_distintivas' => 'nullable|string',
        'microchip' => 'nullable|string|max:255|unique:mascotas,microchip',
        'numero_registro' => 'nullable|string|max:255|unique:mascotas,numero_registro',
        'foto' => 'nullable|image|max:2048',
        'esterilizado' => 'boolean',
        'fecha_esterilizacion' => 'nullable|date|before_or_equal:today',
        'notas_generales' => 'nullable|string',
        'alergias_conocidas' => 'nullable|string',
        'condiciones_cronicas' => 'nullable|string',
        'nivel_agresividad' => 'required|in:tranquilo,nervioso,agresivo_leve,agresivo',
        'propietario_id' => 'nullable|exists:propietarios,id',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre de la mascota es obligatorio',
        'especie_id.required' => 'Debe seleccionar una especie',
        'especie_id.exists' => 'La especie seleccionada no es válida',
        'sexo.required' => 'Debe seleccionar el sexo',
        'microchip.unique' => 'Este número de microchip ya está registrado',
        'numero_registro.unique' => 'Este número de registro ya está registrado',
    ];

    public function updatedEspecieId()
    {
        $this->raza_id = null;
        if ($this->especie_id) {
            $this->razas = Raza::withoutGlobalScopes()
                ->where('especie_id', $this->especie_id)
                ->activas()
                ->ordenadas()
                ->get();
        } else {
            $this->razas = [];
        }
    }

    public function toggleNuevoPropietario()
    {
        $this->showNuevoPropietario = !$this->showNuevoPropietario;
        if (!$this->showNuevoPropietario) {
            $this->reset(['prop_nombres', 'prop_apellidos', 'prop_documento_identidad',
                         'prop_telefono', 'prop_email', 'prop_direccion']);
        }
    }

    public function save()
    {
        $this->validate();

        // Si hay nuevo propietario, crearlo primero
        if ($this->showNuevoPropietario) {
            $this->validate([
                'prop_nombres' => 'required|string|max:255',
                'prop_apellidos' => 'required|string|max:255',
                'prop_telefono' => 'nullable|string|max:20',
                'prop_email' => 'nullable|email|max:255',
            ], [
                'prop_nombres.required' => 'El nombre del propietario es obligatorio',
                'prop_apellidos.required' => 'El apellido del propietario es obligatorio',
            ]);

            $propietario = Propietario::create([
                'nombres' => $this->prop_nombres,
                'apellidos' => $this->prop_apellidos,
                'documento_identidad' => $this->prop_documento_identidad,
                'telefono' => $this->prop_telefono,
                'email' => $this->prop_email,
                'direccion' => $this->prop_direccion,
                'empresa_id' => auth()->user()->empresa_id ?? null,
                'sucursal_id' => auth()->user()->sucursal_id ?? null,
            ]);

            $this->propietario_id = $propietario->id;
        }

        // Subir foto si existe
        $fotoPath = null;
        if ($this->foto) {
            $fotoPath = $this->foto->store('mascotas', 'public');
        }

        // Crear mascota
        $mascota = Mascota::create([
            'nombre' => $this->nombre,
            'especie_id' => $this->especie_id,
            'raza_id' => $this->raza_id,
            'sexo' => $this->sexo,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'peso_actual_kg' => $this->peso_actual_kg,
            'color_pelaje' => $this->color_pelaje,
            'marcas_distintivas' => $this->marcas_distintivas,
            'microchip' => $this->microchip,
            'numero_registro' => $this->numero_registro,
            'foto' => $fotoPath,
            'esterilizado' => $this->esterilizado,
            'fecha_esterilizacion' => $this->fecha_esterilizacion,
            'propietario_id' => $this->propietario_id,
            'notas_generales' => $this->notas_generales,
            'alergias_conocidas' => $this->alergias_conocidas,
            'condiciones_cronicas' => $this->condiciones_cronicas,
            'nivel_agresividad' => $this->nivel_agresividad,
            'empresa_id' => auth()->user()->empresa_id ?? null,
            'sucursal_id' => auth()->user()->sucursal_id ?? null,
        ]);

        session()->flash('message', '✅ Mascota creada exitosamente');
        return redirect()->route('admin.mascotas.index');
    }

    public function render()
    {
        // Usar withoutGlobalScopes para obtener todas las especies activas
        $especies = Especie::withoutGlobalScopes()
            ->activas()
            ->ordenadas()
            ->get();

        $propietarios = Propietario::activos()
            ->forUser()
            ->orderBy('nombres')
            ->get();

        return view('livewire.admin.mascotas.create', [
            'especies' => $especies,
            'razas' => $this->razas,
            'propietarios' => $propietarios,
        ])->layout($this->getLayout());
    }
}
