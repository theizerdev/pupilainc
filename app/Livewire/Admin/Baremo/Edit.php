<?php

namespace App\Livewire\Admin\Baremo;

use App\Models\Baremo;
use App\Models\Categoria;
use App\Models\Especialidad;
use App\Models\ExchangeRate;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Edit extends Component
{
    use HasDynamicLayout;

    public $baremo;
    public $categoria_id = '';
    public $especialidad_id = '';
    public $codigo = '';
    public $nombre_servicio = '';
    public $descripcion = '';
    public $costo_usd = 0;
    public $aplica_iva = true;
    public $exento_iva = false;
    public $duracion_minutos = 30;
    public $porcentaje_medico = 60.00;
    public $porcentaje_clinica = 40.00;
    public $activo = true;
    
    public $empresa_id = null;
    public $sucursal_id = null;
    public $empresas = [];
    public $sucursales = [];
    public $categorias = [];
    public $especialidades = [];
    public $tasa_usd = 1;

    protected function rules()
    {
        return [
            'categoria_id' => 'required|exists:categorias,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'codigo' => 'required|numeric|digits:6|unique:baremos,codigo,' . $this->baremo->id,
            'nombre_servicio' => 'required|min:3|max:150',
            'descripcion' => 'nullable|max:500',
            'costo_usd' => 'required|numeric|gt:0',
            'aplica_iva' => 'boolean',
            'exento_iva' => 'boolean',
            'duracion_minutos' => 'nullable|integer|min:5',
            'porcentaje_medico' => 'required|numeric|min:0|max:100',
            'porcentaje_clinica' => 'required|numeric|min:0|max:100',
            'activo' => 'boolean',
            'empresa_id' => 'required_if:auth.user.role,Super Administrador|exists:empresas,id',
            'sucursal_id' => 'required_if:auth.user.role,Super Administrador|exists:sucursales,id',
        ];
    }

    public function mount(Baremo $baremo)
    {
        $this->baremo = $baremo;
        
        $this->categoria_id = $baremo->categoria_id;
        $this->especialidad_id = $baremo->especialidad_id;
        $this->codigo = (int) $baremo->codigo; // Convertir a entero para mostrar sin ceros
        $this->nombre_servicio = $baremo->nombre_servicio;
        $this->descripcion = $baremo->descripcion;
        $this->costo_usd = $baremo->costo_usd;
        $this->aplica_iva = $baremo->aplica_iva;
        $this->exento_iva = $baremo->exento_iva;
        $this->duracion_minutos = $baremo->duracion_minutos;
        $this->porcentaje_medico = $baremo->porcentaje_medico ?? 60.00;
        $this->porcentaje_clinica = $baremo->porcentaje_clinica ?? 40.00;
        $this->activo = $baremo->activo;
        $this->empresa_id = $baremo->empresa_id;
        $this->sucursal_id = $baremo->sucursal_id;

        $this->categorias = Categoria::forUser()->activos()->orderBy('nombre')->get();
        $this->especialidades = Especialidad::where('status', true)->orderBy('nombre')->get();
        $this->tasa_usd = ExchangeRate::getLatestRate('USD') ?? 1;

        if (auth()->user()->hasRole('Super Administrador')) {
            $this->empresas = \App\Models\Empresa::all();
            if ($this->empresa_id) {
                $this->sucursales = \App\Models\Sucursal::where('empresa_id', $this->empresa_id)->get();
            }
        }
    }

    public function generarCodigo()
    {
        // Generar código de 6 dígitos aleatorios
        do {
            $this->codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Baremo::where('codigo', $this->codigo)->where('id', '!=', $this->baremo->id)->exists());
    }

    public function updatedPorcentajeMedico()
    {
        if ($this->porcentaje_medico !== null && $this->porcentaje_medico >= 0 && $this->porcentaje_medico <= 100) {
            $this->porcentaje_clinica = 100 - $this->porcentaje_medico;
        }
    }

    public function updatedPorcentajeClinica()
    {
        if ($this->porcentaje_clinica !== null && $this->porcentaje_clinica >= 0 && $this->porcentaje_clinica <= 100) {
            $this->porcentaje_medico = 100 - $this->porcentaje_clinica;
        }
    }

    public function updatedEmpresaId()
    {
        if ($this->empresa_id) {
            $this->sucursales = \App\Models\Sucursal::where('empresa_id', $this->empresa_id)->get();
            $this->sucursal_id = null;
        } else {
            $this->sucursales = [];
        }
    }

    public function update()
    {
        // Validar que los porcentajes sumen 100%
        if (abs(($this->porcentaje_medico + $this->porcentaje_clinica) - 100) > 0.01) {
            $this->addError('porcentaje_medico', 'Los porcentajes deben sumar exactamente 100%');
            return;
        }

        $this->validate();

        try {
            $this->baremo->update([
                'categoria_id' => $this->categoria_id,
                'especialidad_id' => $this->especialidad_id,
                'codigo' => str_pad($this->codigo, 6, '0', STR_PAD_LEFT),
                'nombre_servicio' => trim($this->nombre_servicio),
                'descripcion' => trim($this->descripcion),
                'costo_usd' => $this->costo_usd,
                'aplica_iva' => $this->aplica_iva,
                'exento_iva' => $this->exento_iva,
                'duracion_minutos' => $this->duracion_minutos ?? 30,
                'porcentaje_medico' => $this->porcentaje_medico,
                'porcentaje_clinica' => $this->porcentaje_clinica,
                'activo' => $this->activo,
                'empresa_id' => $this->empresa_id,
                'sucursal_id' => $this->sucursal_id,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Servicio '{$this->nombre_servicio}' actualizado exitosamente.",
                'duration' => 4000
            ]);

            return redirect()->route('admin.baremos.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el servicio: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.baremo.edit')->layout($this->getLayout());
    }
}
