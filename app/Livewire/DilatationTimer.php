<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Consulta;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\DilatacionService;

class DilatationTimer extends Component
{
    // Configuración del componente
    public $position = 'bottom-right'; // bottom-right, bottom-left, top-right, top-left
    public $size = 'normal'; // small, normal, large
    public $theme = 'default'; // default, dark, light
    public $autoExpand = false; // Si debe expandirse automáticamente
    public $showDebug = false; // Mostrar información de debug
    public $maxItems = 10; // Máximo número de items a mostrar
    public $filterByUser = false; // Filtrar solo consultas del usuario actual
    public $filterByMedico = false; // Filtrar solo consultas donde el usuario es el médico

    // Estado del componente
    public $activeDilatations = [];
    public $hasActiveDilatations = false;
    public $isExpanded = false;
    public $lastUpdate = 0; // Timestamp para forzar actualizaciones
    public $totalActive = 0;
    public $aplicada = false;

    protected $listeners = [
        'notify-completed' => 'notifyCompleted',
        'refresh-dilatations' => 'loadActiveDilatations',
    ];

    public function mount($config = [])
    {
        // Aplicar configuración personalizada
        $this->position = $config['position'] ?? $this->position;
        $this->size = $config['size'] ?? $this->size;
        $this->theme = $config['theme'] ?? $this->theme;
        $this->autoExpand = $config['autoExpand'] ?? $this->autoExpand;
        $this->showDebug = $config['showDebug'] ?? $this->showDebug;
        $this->maxItems = $config['maxItems'] ?? $this->maxItems;
        $this->filterByUser = $config['filterByUser'] ?? $this->filterByUser;
        $this->filterByMedico = $config['filterByMedico'] ?? $this->filterByMedico;

        // Auto-expandir si está configurado
        $this->isExpanded = $this->autoExpand;

        if (Auth::check()) {
            $this->loadActiveDilatations();
        }
    }

    /**
     * Toggle expand/collapse del widget
     */
    public function toggleExpand()
    {
        $this->isExpanded = !$this->isExpanded;
    }

    /**
     * Cargar consultas activas en estado "en_gotas" con tiempo de espera
     */
    public function loadActiveDilatations()
    {
        if (!Auth::check()) return;

        $user = Auth::user();

        // Construir query base
        $query = Consulta::where('estado', Consulta::ESTADO_EN_GOTAS)
            ->where('empresa_id', $user->empresa_id)
            ->where('sucursal_id', $user->sucursal_id)
            ->with(['paciente', 'medico']);

        // Aplicar filtros
        if ($this->filterByUser) {
            // Filtrar consultas creadas por el usuario actual
            $query->where('created_by', $user->id);
        }

        if ($this->filterByMedico) {
            // Filtrar consultas donde el usuario actual es el médico asignado
            $query->whereHas('medico', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $dilatations = $query->latest()
            
            ->limit($this->maxItems)
            ->get()
            ->map(function ($consulta) {
                $timerData = $this->buildDilatationTimerData($consulta);
                if (!$timerData) {
                    return null;
                }

                return array_merge([
                    'consulta_id' => $consulta->id,
                    'paciente' => $consulta->paciente->nombre_completo ?? 'N/A',
                    'medico_id' => $consulta->medico_id,
                    'medico_user_id' => $consulta->medico ? $consulta->medico->user_id : null,
                    'medico_nombre' => $consulta->medico ? 'Dr(a). ' . $consulta->medico->nombre_completo : 'Sin médico asignado',
                ], $timerData);
            })
            ->filter()
            ->values()
            ->toArray();

        $this->activeDilatations = $dilatations;
        $this->hasActiveDilatations = count($dilatations) > 0;
        $this->totalActive = count($dilatations);
        $this->lastUpdate = now()->timestamp;

        // Verificar y enviar notificaciones para timers que llegaron a cero
        $this->verificarYEnviarNotificaciones();
    }

    /**
     * Construir los datos del timer usando la misma lógica exacta que ProcesoConsulta
     */
    public function buildDilatationTimerData(Consulta $consulta)
    {
        $gotaAplicada = $consulta->gotasAplicadas()
            ->where('estado', 'aplicada')
            ->latest('updated_at')
            ->first();

        $tiempoEspera = $gotaAplicada ? $gotaAplicada->tiempo_espera : 30;
        $fechaInicio = $gotaAplicada ? ($gotaAplicada->updated_at ?? $gotaAplicada->created_at) : ($consulta->estado_changed_at ?? $consulta->updated_at);

        if (!$fechaInicio) {
            return null;
        }

        $segundosTotales = $tiempoEspera * 60;
        $segundosTranscurridos = 0;
        if ($fechaInicio) {
            $fechaInicioCarbon = Carbon::parse($fechaInicio);
            $segundosTranscurridos = $fechaInicioCarbon->gt(now())
                ? 0
                : $fechaInicioCarbon->diffInSeconds(now());
        }

        $segundosRestantes = max(0, $segundosTotales - $segundosTranscurridos);
        $minutosRestantes = floor($segundosRestantes / 60);
        $segundosFormato = $segundosRestantes % 60;
        $porcentaje = $segundosTotales > 0 ? min(100, ($segundosTranscurridos / $segundosTotales) * 100) : 100;
        $yaNotificado = $this->verificarNotificacionEnviada($consulta->id);

        return [
            'tiempo_espera' => $tiempoEspera,
            'segundos_transcurridos' => $segundosTranscurridos,
            'segundos_restantes' => $segundosRestantes,
            'minutos_restantes' => $minutosRestantes,
            'segundos_formato' => $segundosFormato,
            'porcentaje' => $porcentaje,
            'ya_notificado' => $yaNotificado,
            'urgente' => $segundosRestantes <= 300,
            'fecha_inicio' => $fechaInicio ? $fechaInicio->format('Y-m-d H:i:s') : null,
            'tiempo_espera_minutos' => $tiempoEspera,
        ];
    }

    /**
     * Verificar si ya se envió notificación para esta consulta
     */
    private function verificarNotificacionEnviada($consultaId)
    {
        // Verificar en la tabla consulta_gotas si alguna gota ya fue notificada
        $gotaNotificada = Consulta::find($consultaId)
            ->gotasAplicadas()
            ->where('estado', 'aplicada')
            ->where('notificado', true)
            ->exists();

        return $gotaNotificada;
    }

    /**
     * Verificar y enviar notificaciones cuando el timer llega a cero
     */
    public function verificarYEnviarNotificaciones()
    {
        foreach ($this->activeDilatations as $dilatation) {
            // Si el tiempo llegó a cero y aún no se notificó
            if ($dilatation['segundos_restantes'] <= 0 && !$dilatation['ya_notificado']) {
                \Log::info('Dilatación expirada en componente global', [
                    'consulta_id' => $dilatation['consulta_id'],
                    'paciente' => $dilatation['paciente']
                ]);

                // Usar el DilatacionService para procesar la dilatación
                $consulta = Consulta::find($dilatation['consulta_id']);
                if ($consulta) {
                    $dilatacionService = new DilatacionService();
                    $dilatacionService->processConsultaDilatada($consulta);

                    // Mostrar notificación al usuario
                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => "¡Dilatación completada! La consulta de {$dilatation['paciente']} ha sido procesada."
                    ]);

                    \Log::info('Dilatación procesada desde componente global', [
                        'consulta_id' => $dilatation['consulta_id']
                    ]);
                }
            }
        }
    }

    /**
     * Enviar notificación vía chat interno y sistema de notificaciones
     */
    /**
     * Notificar cuando un timer llega a cero (método legacy, ahora usa DilatacionService)
     */
    public function notifyCompleted($consultaId)
    {
        // Este método se mantiene por compatibilidad pero ahora el procesamiento
        // se hace automáticamente en verificarYEnviarNotificaciones
        \Log::info('notifyCompleted llamado (legacy)', ['consulta_id' => $consultaId]);
    }

    /**
     * Cambiar configuración del componente dinámicamente
     */
    public function updateConfig($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
            $this->loadActiveDilatations(); // Recargar con nueva configuración
        }
    }

    /**
     * Cambiar posición del widget
     */
    public function setPosition($position)
    {
        $validPositions = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];
        if (in_array($position, $validPositions)) {
            $this->position = $position;
        }
    }

    /**
     * Cambiar tamaño del widget
     */
    public function setSize($size)
    {
        $validSizes = ['small', 'normal', 'large'];
        if (in_array($size, $validSizes)) {
            $this->size = $size;
        }
    }

    /**
     * Forzar actualización manual
     */
    public function refresh()
    {
        $this->loadActiveDilatations();
    }

    /**
     * Obtener configuración actual como array
     */
    public function getConfig()
    {
        return [
            'position' => $this->position,
            'size' => $this->size,
            'theme' => $this->theme,
            'autoExpand' => $this->autoExpand,
            'showDebug' => $this->showDebug,
            'maxItems' => $this->maxItems,
            'filterByUser' => $this->filterByUser,
            'filterByMedico' => $this->filterByMedico,
            'totalActive' => $this->totalActive,
            'hasActiveDilatations' => $this->hasActiveDilatations,
        ];
    }

    public function render()
    {
        return view('livewire.dilatation-timer');
    }
}
