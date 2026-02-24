<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Spatie\Activitylog\Models\Activity;
use Livewire\WithPagination;
use App\Models\User;
use Livewire\Attributes\Url;
use App\Models\Medico;
use App\Models\Cita;
use App\Models\AuditLog;
use App\Traits\HasSpanishActivityLog;

class ActivityLog extends Component
{
    use WithPagination;
    use HasDynamicLayout;

    #[Url]
    public $search = '';
    
    #[Url]
    public $userFilter = '';
    
    #[Url]
    public $dateRange = '';
    
    #[Url]
    public $actionFilter = '';
    
    #[Url]
    public $subjectTypeFilter = '';
    
    #[Url]
    public $sortBy = 'created_at';
    
    #[Url]
    public $sortDirection = 'desc';
    
    #[Url]
    public $doctorFilter = '';
    
    #[Url]
    public $onlyCitaEstados = false;
    
    #[Url]
    public $securityFilter = '';
    
    public $perPage = 10;
    
    public $selectedActivities = [];
    
    public $selectAll = false;

    protected $queryString = ['search', 'userFilter', 'dateRange', 'actionFilter', 'subjectTypeFilter', 'sortBy', 'sortDirection', 'doctorFilter', 'onlyCitaEstados', 'securityFilter'];

    public function render()
    {
        $activities = Activity::with(['causer', 'subject'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('subject_type', 'like', '%' . $this->search . '%')
                      ->orWhereHas('causer', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->userFilter, function ($query) {
                $query->where('causer_id', $this->userFilter);
            })
            ->when($this->actionFilter, function ($query) {
                $query->where('description', $this->actionFilter);
            })
            ->when($this->subjectTypeFilter, function ($query) {
                $query->where('subject_type', 'like', '%' . $this->subjectTypeFilter . '%');
            })
            ->when($this->dateRange, function ($query) {
                $dates = match($this->dateRange) {
                    'today' => [now()->startOfDay(), now()->endOfDay()],
                    'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                    'week' => [now()->startOfWeek(), now()->endOfWeek()],
                    'month' => [now()->startOfMonth(), now()->endOfMonth()],
                    'last7days' => [now()->subDays(7), now()],
                    'last30days' => [now()->subDays(30), now()],
                    default => null,
                };

                if ($dates) {
                    $query->whereBetween('created_at', $dates);
                }
            })
            ->when($this->onlyCitaEstados, function ($query) {
                $query->whereIn('description', ['cita_estado_cambiado', 'cita_estado_actualizado_automaticamente'])
                      ->where('subject_type', Cita::class);
            })
            ->when($this->doctorFilter, function ($query) {
                $query->whereHas('subject', function ($q) {
                    $q->where('medico_id', $this->doctorFilter);
                });
            })
            ->when($this->sortBy, function ($query) {
                $query->orderBy($this->sortBy, $this->sortDirection);
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->paginate($this->perPage);

        $users = User::orderBy('name')->get();
        $medicos = Medico::orderBy('nombres')->get();
        
        $actions = Activity::select('description')
            ->distinct()
            ->orderBy('description')
            ->pluck('description', 'description')
            ->mapWithKeys(function ($item) {
                return [$item => ucfirst($item)];
            });
            
        $subjectTypes = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->orderBy('subject_type')
            ->pluck('subject_type', 'subject_type')
            ->mapWithKeys(function ($item) {
                $className = class_basename($item);
                return [$item => $className];
            });

        // Eventos de seguridad desde audit_logs
        $securityEvents = collect();
        if ($this->securityFilter) {
            $securityQuery = AuditLog::query()
                ->where('action', 'like', 'seguridad.%')
                ->when($this->securityFilter === 'login_fallido', fn($q) => $q->where('action', 'seguridad.login_fallido'))
                ->when($this->securityFilter === 'usuario_bloqueado', fn($q) => $q->where('action', 'seguridad.usuario_bloqueado'))
                ->when($this->securityFilter === 'acceso_no_autorizado', fn($q) => $q->where('action', 'seguridad.acceso_no_autorizado'))
                ->when($this->securityFilter === 'restriccion', fn($q) => $q->where('action', 'seguridad.restriccion'))
                ->when($this->securityFilter === 'acceso_denegado', fn($q) => $q->where('action', 'seguridad.acceso_denegado'))
                ->when($this->securityFilter === 'acceso_bloqueado', fn($q) => $q->where('action', 'seguridad.acceso_bloqueado'))
                ->when($this->securityFilter === 'todos', fn($q) => $q)
                ->when($this->search, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('action', 'like', '%' . $this->search . '%')
                            ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                            ->orWhereRaw("JSON_EXTRACT(new_values, '$.identificador') LIKE ?", ['%' . $this->search . '%']);
                    });
                })
                ->when($this->dateRange, function ($q) {
                    $dates = match($this->dateRange) {
                        'today' => [now()->startOfDay(), now()->endOfDay()],
                        'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                        'week' => [now()->startOfWeek(), now()->endOfWeek()],
                        'month' => [now()->startOfMonth(), now()->endOfMonth()],
                        'last7days' => [now()->subDays(7), now()],
                        'last30days' => [now()->subDays(30), now()],
                        default => null,
                    };
                    if ($dates) {
                        $q->whereBetween('created_at', $dates);
                    }
                })
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);

            $securityEvents = $securityQuery;
        }

        // Contadores de seguridad
        $securityCounts = [
            'login_fallido' => AuditLog::where('action', 'seguridad.login_fallido')->count(),
            'usuario_bloqueado' => AuditLog::where('action', 'seguridad.usuario_bloqueado')->count(),
            'acceso_no_autorizado' => AuditLog::where('action', 'seguridad.acceso_no_autorizado')->count(),
            'restriccion' => AuditLog::where('action', 'seguridad.restriccion')->count(),
            'total' => AuditLog::where('action', 'like', 'seguridad.%')->count(),
        ];

        return view('livewire.admin.activity-log', compact('activities', 'users', 'actions', 'subjectTypes', 'medicos', 'securityEvents', 'securityCounts'))
            ->layout($this->getLayout(), ['title' => 'Seguimiento de Actividades']);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingUserFilter()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function updatingSubjectTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function updatingSecurityFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedActivities = $this->activities->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedActivities = [];
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->userFilter = '';
        $this->dateRange = '';
        $this->actionFilter = '';
        $this->subjectTypeFilter = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->securityFilter = '';
        $this->resetPage();
        
        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Filtros limpiados correctamente'
        ]);
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedActivities)) {
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => 'No hay actividades seleccionadas'
            ]);
            return;
        }

        try {
            $count = Activity::whereIn('id', $this->selectedActivities)->delete();
            
            $this->selectedActivities = [];
            $this->selectAll = false;
            
            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => "Se eliminaron {$count} actividades correctamente"
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error al eliminar actividades: ' . $e->getMessage()
            ]);
        }
    }

    public function getActionColor($action)
    {
        if (str_contains($action, 'creó') || $action === 'created') return 'success';
        if (str_contains($action, 'editó') || $action === 'updated') return 'warning';
        if (str_contains($action, 'eliminó') || $action === 'deleted' || $action === 'force-deleted') return 'danger';
        if ($action === 'restored') return 'info';
        if ($action === 'login') return 'primary';
        if ($action === 'logout') return 'secondary';
        if ($action === 'password-updated') return 'warning';
        if ($action === 'profile-updated') return 'info';
        return 'primary';
    }

    public function getActionIcon($action)
    {
        if (str_contains($action, 'creó') || $action === 'created') return 'plus';
        if (str_contains($action, 'editó') || $action === 'updated') return 'edit';
        if (str_contains($action, 'eliminó') || $action === 'deleted' || $action === 'force-deleted') return 'trash';
        if ($action === 'restored') return 'undo';
        if ($action === 'login') return 'sign-in-alt';
        if ($action === 'logout') return 'sign-out-alt';
        if ($action === 'password-updated') return 'key';
        if ($action === 'profile-updated') return 'user-edit';
        return 'circle';
    }

    public function export($format = 'csv')
    {
        try {
            $activities = Activity::with(['causer', 'subject'])
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('description', 'like', '%' . $this->search . '%')
                          ->orWhere('subject_type', 'like', '%' . $this->search . '%')
                          ->orWhereHas('causer', function ($q) {
                              $q->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                          });
                    });
                })
                ->when($this->userFilter, function ($query) {
                    $query->where('causer_id', $this->userFilter);
                })
                ->when($this->actionFilter, function ($query) {
                    $query->where('description', $this->actionFilter);
                })
                ->when($this->subjectTypeFilter, function ($query) {
                    $query->where('subject_type', 'like', '%' . $this->subjectTypeFilter . '%');
                })
                ->when($this->dateRange, function ($query) {
                    $dates = match($this->dateRange) {
                        'today' => [now()->startOfDay(), now()->endOfDay()],
                        'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                        'week' => [now()->startOfWeek(), now()->endOfWeek()],
                        'month' => [now()->startOfMonth(), now()->endOfMonth()],
                        'last7days' => [now()->subDays(7), now()],
                        'last30days' => [now()->subDays(30), now()],
                        default => null,
                    };

                    if ($dates) {
                        $query->whereBetween('created_at', $dates);
                    }
                })
                ->when($this->sortBy, function ($query) {
                    $query->orderBy($this->sortBy, $this->sortDirection);
                }, function ($query) {
                    $query->orderBy('created_at', 'desc');
                })
                ->get();

            if ($activities->isEmpty()) {
                $this->dispatch('showToast', [
                    'type' => 'warning',
                    'message' => 'No hay actividades para exportar con los filtros actuales'
                ]);
                return;
            }

            $filename = 'activity_log_' . now()->format('Y-m-d_H-i-s');

            if ($format === 'csv') {
                return $this->exportToCsv($activities, $filename);
            } elseif ($format === 'json') {
                return $this->exportToJson($activities, $filename);
            } elseif ($format === 'xml') {
                return $this->exportToXml($activities, $filename);
            }

        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error al exportar: ' . $e->getMessage()
            ]);
        }
    }

    protected function exportToCsv($activities, $filename)
    {
        $csv = fopen('php://temp', 'w+');
        
        fputcsv($csv, [
            'Usuario', 
            'Acción', 
            'Modelo', 
            'ID Modelo', 
            'Fecha', 
            'IP', 
            'User Agent',
            'Propiedades'
        ]);
        
        foreach ($activities as $activity) {
            fputcsv($csv, [
                $activity->causer?->name ?? 'Sistema',
                $activity->description,
                class_basename($activity->subject_type),
                $activity->subject_id,
                $activity->created_at->format('d/m/Y H:i:s'),
                $activity->properties->get('ip_address', 'N/A'),
                $activity->properties->get('user_agent', 'N/A'),
                json_encode($activity->properties->except(['ip_address', 'user_agent']))
            ]);
        }
        
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);
        
        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Exportación CSV completada exitosamente'
        ]);
        
        return response()->stream(function() use ($csvContent) {
            echo $csvContent;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    protected function exportToJson($activities, $filename)
    {
        $data = $activities->map(function ($activity) {
            return [
                'usuario' => $activity->causer?->name ?? 'Sistema',
                'accion' => $activity->description,
                'modelo' => class_basename($activity->subject_type),
                'modelo_id' => $activity->subject_id,
                'fecha' => $activity->created_at->format('Y-m-d H:i:s'),
                'ip' => $activity->properties->get('ip_address', 'N/A'),
                'user_agent' => $activity->properties->get('user_agent', 'N/A'),
                'propiedades' => $activity->properties->except(['ip_address', 'user_agent'])->toArray()
            ];
        });

        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Exportación JSON completada exitosamente'
        ]);

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '.json"',
            'Content-Type' => 'application/json',
        ]);
    }

    protected function exportToXml($activities, $filename)
    {
        $xml = new \SimpleXMLElement('<activities></activities>');
        
        foreach ($activities as $activity) {
            $activityElement = $xml->addChild('activity');
            $activityElement->addChild('usuario', htmlspecialchars($activity->causer?->name ?? 'Sistema'));
            $activityElement->addChild('accion', htmlspecialchars($activity->description));
            $activityElement->addChild('modelo', htmlspecialchars(class_basename($activity->subject_type)));
            $activityElement->addChild('modelo_id', $activity->subject_id);
            $activityElement->addChild('fecha', $activity->created_at->format('Y-m-d H:i:s'));
            $activityElement->addChild('ip', htmlspecialchars($activity->properties->get('ip_address', 'N/A')));
            $activityElement->addChild('user_agent', htmlspecialchars($activity->properties->get('user_agent', 'N/A')));
            
            $properties = $activityElement->addChild('propiedades');
            foreach ($activity->properties->except(['ip_address', 'user_agent']) as $key => $value) {
                $properties->addChild($key, htmlspecialchars((string) $value));
            }
        }

        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Exportación XML completada exitosamente'
        ]);

        return response()->stream(function() use ($xml) {
            echo $xml->asXML();
        }, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xml"',
        ]);
    }
}
