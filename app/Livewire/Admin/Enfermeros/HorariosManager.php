<?php

namespace App\Livewire\Admin\Enfermeros;

use App\Models\Enfermero;
use App\Models\EnfermeroHorario;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class HorariosManager extends Component
{
    use HasDynamicLayout;

    public $enfermero;
    public $horarios = [];
    public $resumenHorarios = [];

    public function mount($id)
    {
        $this->enfermero = Enfermero::findOrFail($id);
        $this->cargarHorarios();
    }

    private function cargarHorarios()
    {
        $diasSemana = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes',
            6 => 'Sábado', 7 => 'Domingo'
        ];
        
        foreach ($diasSemana as $dia => $nombre) {
            $horario = $this->enfermero->horarios()->where('dia_semana', $dia)->first();
            
            if ($horario) {
                $this->horarios[$dia] = [
                    'activo' => $horario->activo,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'duracion_cita' => $horario->duracion_cita,
                ];
            } else {
                $this->horarios[$dia] = [
                    'activo' => in_array($dia, [1, 2, 3, 4, 5]), // Lunes a Viernes activos por defecto
                    'hora_inicio' => '08:00',
                    'hora_fin' => '16:00',
                    'duracion_cita' => 30,
                ];
            }
        }
        
        $this->calcularResumenHorarios();
    }

    private function calcularResumenHorarios()
    {
        $diasActivos = 0;
        $horasSemanales = 0;
        $totalDuracion = 0;
        $diasConDuracion = 0;

        foreach ($this->horarios as $dia => $horario) {
            if ($horario['activo']) {
                $diasActivos++;
                
                // Calcular horas por día
                $horaInicio = strtotime($horario['hora_inicio']);
                $horaFin = strtotime($horario['hora_fin']);
                $horasPorDia = ($horaFin - $horaInicio) / 3600; // Convertir a horas
                $horasSemanales += $horasPorDia;
            }
            
            // Acumular duración para promedio
            if (isset($horario['duracion_cita'])) {
                $totalDuracion += $horario['duracion_cita'];
                $diasConDuracion++;
            }
        }

        $promedioDuracion = $diasConDuracion > 0 ? round($totalDuracion / $diasConDuracion) : 30;

        $this->resumenHorarios = [
            'dias_activos' => $diasActivos,
            'horas_semanales' => round($horasSemanales, 1),
            'promedio_duracion' => $promedioDuracion,
        ];
    }

    public function actualizarEstadoDia($dia)
    {
        $this->calcularResumenHorarios();
    }

    public function copiarHorario($diaOrigen)
    {
        if (!isset($this->horarios[$diaOrigen])) {
            return;
        }

        $horarioOrigen = $this->horarios[$diaOrigen];
        
        foreach ($this->horarios as $dia => &$horario) {
            if ($dia != $diaOrigen) {
                $horario['hora_inicio'] = $horarioOrigen['hora_inicio'];
                $horario['hora_fin'] = $horarioOrigen['hora_fin'];
                $horario['duracion_cita'] = $horarioOrigen['duracion_cita'];
            }
        }
        
        $this->calcularResumenHorarios();
    }

    public function activarDiasHabiles()
    {
        foreach ($this->horarios as $dia => &$horario) {
            if (in_array($dia, [1, 2, 3, 4, 5])) { // Lunes a Viernes
                $horario['activo'] = true;
            }
        }
        
        $this->calcularResumenHorarios();
    }

    public function desactivarTodos()
    {
        foreach ($this->horarios as $dia => &$horario) {
            $horario['activo'] = false;
        }
        
        $this->calcularResumenHorarios();
    }

    public function guardarHorarios()
    {
        //\Gate::authorize('update enfermeros');
        
        try {
            foreach ($this->horarios as $dia => $horario) {
                $this->enfermero->horarios()->updateOrCreate(
                    ['dia_semana' => $dia],
                    [
                        'hora_inicio' => $horario['hora_inicio'],
                        'hora_fin' => $horario['hora_fin'],
                        'duracion_cita' => $horario['duracion_cita'],
                        'activo' => $horario['activo'],
                        'empresa_id' => $this->enfermero->empresa_id,
                        'sucursal_id' => $this->enfermero->sucursal_id,
                    ]
                );
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Horarios del enfermero/a actualizados exitosamente.',
                'duration' => 5000
            ]);

            return redirect()->route('admin.enfermeros.index');

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar los horarios: ' . $e->getMessage(),
                'duration' => 6000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.enfermeros.horarios-manager', [
            'enfermero' => $this->enfermero,
            'resumenHorarios' => $this->resumenHorarios,
        ])->layout($this->getLayout());
    }
}