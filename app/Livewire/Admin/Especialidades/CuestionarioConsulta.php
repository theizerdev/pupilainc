<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Cuestionario;
use App\Models\Especialidad;
use App\Models\Pregunta;
use App\Traits\HasDynamicLayout;
use Livewire\Component;

class CuestionarioConsulta extends Component
{
    use HasDynamicLayout;

    public Especialidad  $especialidad;
    public ?Cuestionario $cuestionario = null;

    // Datos del cuestionario
    public string $titulo      = '';
    public string $descripcion = '';
    public bool   $activo      = true;

    // Preguntas cargadas
    public array $preguntas = [];

    // Modal pregunta
    public bool    $modalPregunta   = false;
    public ?int    $preguntaEditId  = null;
    public string  $preguntaTitulo  = '';
    public string  $preguntaDesc    = '';
    public string  $preguntaTipo    = 'texto';
    public string  $preguntaOpciones = '';  // una por línea
    public bool    $preguntaObligatorio = true;

    // Tipos disponibles (igual que en Pregunta)
    const TIPOS = [
        'texto'    => 'Texto libre',
        'si_no'    => 'Sí / No',
        'opcion'   => 'Opción única',
        'multiple' => 'Opción múltiple',
        'escala'   => 'Escala 1-10',
    ];

    public function mount(Especialidad $especialidad): void
    {
        $this->especialidad = $especialidad;
        $this->cargarCuestionario();
    }

    // ── Carga ─────────────────────────────────────────────────────────────────

    private function cargarCuestionario(): void
    {
        $this->cuestionario = Cuestionario::with(['preguntas' => fn($q) => $q->orderBy('orden')])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('especialidad_id', $this->especialidad->id)
            ->where('tipo', 'preconsulta')
            ->latest()
            ->first();

        if ($this->cuestionario) {
            $this->titulo      = $this->cuestionario->titulo;
            $this->descripcion = $this->cuestionario->descripcion ?? '';
            $this->activo      = $this->cuestionario->activo;
            $this->sincronizarPreguntas();
        } else {
            $this->titulo = 'Cuestionario de Preconsulta — ' . $this->especialidad->nombre;
        }
    }

    private function sincronizarPreguntas(): void
    {
        $this->preguntas = Pregunta::where('cuestionario_id', $this->cuestionario->id)
            ->orderBy('orden')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'titulo'      => $p->titulo,
                'descripcion' => $p->descripcion,
                'tipo'        => $p->tipo,
                'opciones'    => $p->opciones ?? [],
                'obligatorio' => $p->obligatorio,
                'orden'       => $p->orden,
                'activo'      => $p->activo,
            ])->toArray();
    }

    // ── Cuestionario ──────────────────────────────────────────────────────────

    public function guardarCuestionario(): void
    {
        $this->validate([
            'titulo' => 'required|string|max:255',
        ]);

        if ($this->cuestionario) {
            $this->cuestionario->update([
                'titulo'      => $this->titulo,
                'descripcion' => $this->descripcion,
                'activo'      => $this->activo,
            ]);
            $msg = 'Cuestionario actualizado.';
        } else {
            $this->cuestionario = Cuestionario::create([
                'titulo'          => $this->titulo,
                'descripcion'     => $this->descripcion,
                'tipo'            => 'preconsulta',
                'activo'          => $this->activo,
                'empresa_id'      => auth()->user()->empresa_id,
                'especialidad_id' => $this->especialidad->id,
            ]);
            $msg = 'Cuestionario creado.';
        }

        $this->sincronizarPreguntas();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    // ── Preguntas ─────────────────────────────────────────────────────────────

    public function abrirModalPregunta(?int $preguntaId = null): void
    {
        $this->resetModalPregunta();

        if ($preguntaId) {
            $p = Pregunta::findOrFail($preguntaId);
            $this->preguntaEditId     = $preguntaId;
            $this->preguntaTitulo     = $p->titulo;
            $this->preguntaDesc       = $p->descripcion ?? '';
            $this->preguntaTipo       = $p->tipo;
            $this->preguntaOpciones   = $p->opciones ? implode("\n", $p->opciones) : '';
            $this->preguntaObligatorio = $p->obligatorio;
        }

        $this->modalPregunta = true;
    }

    public function guardarPregunta(): void
    {
        $this->validate([
            'preguntaTitulo' => 'required|string|max:500',
            'preguntaTipo'   => 'required|in:' . implode(',', array_keys(self::TIPOS)),
        ]);

        // Asegurar que el cuestionario existe
        if (!$this->cuestionario) {
            $this->guardarCuestionario();
        }

        $opciones = null;
        if (in_array($this->preguntaTipo, ['opcion', 'multiple']) && !empty($this->preguntaOpciones)) {
            $opciones = array_values(array_filter(
                array_map('trim', explode("\n", $this->preguntaOpciones))
            ));
        }

        $data = [
            'titulo'      => $this->preguntaTitulo,
            'descripcion' => $this->preguntaDesc ?: null,
            'tipo'        => $this->preguntaTipo,
            'opciones'    => $opciones,
            'obligatorio' => $this->preguntaObligatorio,
            'activo'      => true,
        ];

        if ($this->preguntaEditId) {
            Pregunta::findOrFail($this->preguntaEditId)->update($data);
            $msg = 'Pregunta actualizada.';
        } else {
            $data['cuestionario_id'] = $this->cuestionario->id;
            $data['orden']           = count($this->preguntas) + 1;
            Pregunta::create($data);
            $msg = 'Pregunta agregada.';
        }

        $this->sincronizarPreguntas();
        $this->resetModalPregunta();
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function togglePregunta(int $preguntaId): void
    {
        $p = Pregunta::findOrFail($preguntaId);
        $p->update(['activo' => !$p->activo]);
        $this->sincronizarPreguntas();
    }

    public function eliminarPregunta(int $preguntaId): void
    {
        Pregunta::findOrFail($preguntaId)->delete();
        $this->sincronizarPreguntas();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Pregunta eliminada.']);
    }

    public function moverPregunta(int $preguntaId, string $direccion): void
    {
        $preguntas = Pregunta::where('cuestionario_id', $this->cuestionario->id)
            ->orderBy('orden')->get();

        $idx  = $preguntas->search(fn($p) => $p->id === $preguntaId);
        if ($idx === false) return;

        $swap = $direccion === 'up' ? $idx - 1 : $idx + 1;
        if ($swap < 0 || $swap >= $preguntas->count()) return;

        $ordenA = $preguntas[$idx]->orden;
        $ordenB = $preguntas[$swap]->orden;
        $preguntas[$idx]->update(['orden' => $ordenB]);
        $preguntas[$swap]->update(['orden' => $ordenA]);

        $this->sincronizarPreguntas();
    }

    private function resetModalPregunta(): void
    {
        $this->modalPregunta      = false;
        $this->preguntaEditId     = null;
        $this->preguntaTitulo     = '';
        $this->preguntaDesc       = '';
        $this->preguntaTipo       = 'texto';
        $this->preguntaOpciones   = '';
        $this->preguntaObligatorio = true;
    }

    protected function getPageTitle(): string
    {
        return 'Cuestionario: ' . $this->especialidad->nombre;
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard'                                      => 'Dashboard',
            'admin.especialidades.index'                           => 'Especialidades',
            'admin.especialidades.show:' . $this->especialidad->id => $this->especialidad->nombre,
            ''                                                     => 'Cuestionario',
        ];
    }

    public function render()
    {
        return view('livewire.admin.especialidades.cuestionario-consulta', [
            'tipos' => self::TIPOS,
        ])->layout($this->getLayout());
    }
}
