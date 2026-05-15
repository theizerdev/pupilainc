<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Livewire\WithPagination;
use App\Models\ActiveSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

class ActiveSessions extends Component
{
    use WithPagination;
    use HasDynamicLayout;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public $search = '';

    #[Url]
    public $status = '';

    #[Url]
    public $deviceType = '';

    #[Url]
    public $sortBy = 'last_activity';

    #[Url]
    public $sortDirection = 'desc';

    public $perPage = 10;

    public $selectedSessions = [];

    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'deviceType' => ['except' => ''],
        'sortBy' => ['except' => 'last_activity'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10]
    ];

    public function mount()
    {
        // Verificar permiso para ver sesiones activas
        if (!Auth::user()->can('view active sessions')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }

    public function loadSessions()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
    }

    public function render()
    {
        $query = ActiveSession::with('user')
            ->where('user_id', '!=', null)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                    ->orWhere('location', 'like', '%' . $this->search . '%')
                    ->orWhere('user_agent', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                if ($this->status === 'current') {
                    $query->where('is_current', true);
                } elseif ($this->status === 'active') {
                    $query->where('is_active', true)->where('is_current', false);
                } elseif ($this->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->when($this->deviceType, function ($query) {
                if ($this->deviceType === 'mobile') {
                    $query->where('user_agent', 'like', '%mobile%');
                } elseif ($this->deviceType === 'tablet') {
                    $query->where('user_agent', 'like', '%tablet%');
                } elseif ($this->deviceType === 'desktop') {
                    $query->where(function ($q) {
                        $q->where('user_agent', 'not like', '%mobile%')
                          ->where('user_agent', 'not like', '%tablet%');
                    });
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $sessions = $query->paginate($this->perPage);

        // Estadísticas
        $stats = [
            'total' => ActiveSession::where('user_id', '!=', null)->count(),
            'active' => ActiveSession::where('is_active', true)->count(),
            'current' => ActiveSession::where('is_current', true)->count(),
            'mobile' => ActiveSession::where('user_agent', 'like', '%mobile%')->count()
        ];

        return $this->renderWithLayout('livewire.admin.active-sessions', [
            'sessions' => $sessions,
            'stats' => $stats
        ], [
            'title' => 'Gestión de Sesiones Activas',
            'description' => 'Administra y monitorea las sesiones activas del sistema',
            'breadcrumb' => [
                'admin.dashboard' => 'Dashboard',
                'admin.active-sessions' => 'Sesiones Activas'
            ]
        ]);
    }

    public function terminateSession($sessionId)
    {
        if (!auth()->user()->can('view active sessions')) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'No tienes permisos para gestionar sesiones.'
            ]);
            return;
        }

        try {
            $session = ActiveSession::findOrFail($sessionId);

            if ($session->is_current) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'message' => 'No puedes terminar tu sesión actual desde aquí.'
                ]);
                return;
            }

            $session->delete();
            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Sesión terminada exitosamente.'
            ]);
            $this->dispatch('sessionTerminated');

        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error al terminar la sesión: ' . $e->getMessage()
            ]);
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'deviceType', 'sortBy', 'sortDirection']);
        $this->resetPage();
        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Filtros limpiados correctamente'
        ]);
        $this->dispatch('filtersCleared');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedSessions = ActiveSession::where('user_id', '!=', null)
                ->where('is_current', false)
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedSessions = [];
        }
    }

    public function bulkTerminateSessions()
    {
        if (!auth()->user()->can('view active sessions')) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'No tienes permisos para gestionar sesiones.'
            ]);
            return;
        }

        if (empty($this->selectedSessions)) {
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => 'Por favor selecciona al menos una sesión para terminar.'
            ]);
            return;
        }

        try {
            $count = ActiveSession::whereIn('id', $this->selectedSessions)
                ->where('is_current', false)
                ->delete();

            $this->selectedSessions = [];
            $this->selectAll = false;

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => "{$count} sesiones terminadas exitosamente."
            ]);
            $this->dispatch('sessionsTerminated', ['count' => $count]);

        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error al terminar las sesiones: ' . $e->getMessage()
            ]);
        }
    }

    public function exportSessions($format)
    {
        try {
            $sessions = ActiveSession::with('user')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->whereHas('user', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('email', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                        ->orWhere('location', 'like', '%' . $this->search . '%')
                        ->orWhere('user_agent', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->status, function ($query) {
                    if ($this->status === 'current') {
                        $query->where('is_current', true);
                    } elseif ($this->status === 'active') {
                        $query->where('is_active', true)->where('is_current', false);
                    } elseif ($this->status === 'inactive') {
                        $query->where('is_active', false);
                    }
                })
                ->when($this->deviceType, function ($query) {
                    if ($this->deviceType === 'mobile') {
                        $query->where('user_agent', 'like', '%mobile%');
                    } elseif ($this->deviceType === 'tablet') {
                        $query->where('user_agent', 'like', '%tablet%');
                    } elseif ($this->deviceType === 'desktop') {
                        $query->where(function ($q) {
                            $q->where('user_agent', 'not like', '%mobile%')
                              ->where('user_agent', 'not like', '%tablet%');
                        });
                    }
                })
                ->orderBy($this->sortBy, $this->sortDirection)
                ->get();

            if ($sessions->isEmpty()) {
                $this->dispatch('showToast', [
                    'type' => 'warning',
                    'message' => 'No hay sesiones para exportar.'
                ]);
                return;
            }

            $filename = 'sesiones_' . now()->format('Y-m-d_H-i-s');

            switch (strtolower($format)) {
                case 'csv':
                    $this->exportToCsv($sessions, $filename);
                    break;
                case 'json':
                    $this->exportToJson($sessions, $filename);
                    break;
                case 'xml':
                    $this->exportToXml($sessions, $filename);
                    break;
                default:
                    throw new \Exception('Formato de exportación no soportado');
            }

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => "Sesiones exportadas en formato {$format->toUpperCase()}."
            ]);
            $this->dispatch('sessionsExported', ['format' => $format]);

        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error al exportar sesiones: ' . $e->getMessage()
            ]);
        }
    }

    protected function exportToCsv($sessions, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}.csv"
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Usuario', 'Email', 'IP', 'Ubicación', 'Dispositivo', 'Última Actividad', 'Estado']);

            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->user->name ?? 'N/A',
                    $session->user->email ?? 'N/A',
                    $session->ip_address,
                    $session->location ?? 'Desconocida',
                    $this->getDeviceType($session->user_agent),
                    $session->last_activity->format('Y-m-d H:i:s'),
                    $session->is_current ? 'Sesión Actual' : ($session->is_active ? 'Activa' : 'Inactiva')
                ]);
            }
            fclose($file);
        };

        response()->stream($callback, 200, $headers)->send();
    }

    protected function exportToJson($sessions, $filename)
    {
        $data = $sessions->map(function ($session) {
            return [
                'usuario' => $session->user->name ?? 'N/A',
                'email' => $session->user->email ?? 'N/A',
                'ip' => $session->ip_address,
                'ubicacion' => $session->location ?? 'Desconocida',
                'dispositivo' => $this->getDeviceType($session->user_agent),
                'ultima_actividad' => $session->last_activity->format('Y-m-d H:i:s'),
                'estado' => $session->is_current ? 'Sesión Actual' : ($session->is_active ? 'Activa' : 'Inactiva'),
                'user_agent' => $session->user_agent
            ];
        });

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename={$filename}.json"
        ];

        return response()->json($data, 200, $headers);
    }

    protected function exportToXml($sessions, $filename)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sesiones></sesiones>');

        foreach ($sessions as $session) {
            $sessionXml = $xml->addChild('sesion');
            $sessionXml->addChild('usuario', htmlspecialchars($session->user->name ?? 'N/A'));
            $sessionXml->addChild('email', htmlspecialchars($session->user->email ?? 'N/A'));
            $sessionXml->addChild('ip', htmlspecialchars($session->ip_address));
            $sessionXml->addChild('ubicacion', htmlspecialchars($session->location ?? 'Desconocida'));
            $sessionXml->addChild('dispositivo', htmlspecialchars($this->getDeviceType($session->user_agent)));
            $sessionXml->addChild('ultima_actividad', $session->last_activity->format('Y-m-d H:i:s'));
            $sessionXml->addChild('estado', htmlspecialchars($session->is_current ? 'Sesión Actual' : ($session->is_active ? 'Activa' : 'Inactiva')));
        }

        $headers = [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => "attachment; filename={$filename}.xml"
        ];

        return response($xml->asXML(), 200, $headers);
    }

    protected function getDeviceType($userAgent)
    {
        if (str_contains(strtolower($userAgent), 'mobile')) {
            return 'Móvil';
        } elseif (str_contains(strtolower($userAgent), 'tablet')) {
            return 'Tablet';
        } else {
            return 'Escritorio';
        }
    }
}
