<?php

namespace App\Livewire\Admin\Enfermeros;

use App\Models\Enfermero;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;
use App\Services\UniversalNotificationService;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $status = '';
    public $empresa_id = '';
    public $sucursal_id = '';
    public $nivel_experiencia = '';
    public $tipo_enfermero = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'status' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => ''],
        'nivel_experiencia' => ['except' => ''],
        'tipo_enfermero' => ['except' => '']
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEmpresaId()
    {
        $this->resetPage();
        $this->sucursal_id = '';
    }

    public function updatingSucursalId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingNivelExperiencia()
    {
        $this->resetPage();
    }

    public function updatingTipoEnfermero()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'empresa_id', 'sucursal_id', 'nivel_experiencia', 'tipo_enfermero']);
        $this->resetPage();
    }

    public function delete($id)
    {
        $this->authorize('edit enfermeros');
        
        try {
            $enfermero = Enfermero::findOrFail($id);
            
            // Verificar si tiene signos vitales asociados antes de eliminar
            if ($enfermero->signosVitales && $enfermero->signosVitales->count() > 0) {
                throw new \Exception('No se puede eliminar un enfermero con signos vitales asociados.');
            }
            
            // Eliminar horarios primero
            $enfermero->horarios()->delete();
            
            // Eliminar especialidades
            $enfermero->especialidades()->delete();
            
            // Eliminar usuario asociado si existe
            if ($enfermero->user) {
                $enfermero->user->delete();
            }
            
            // Finalmente eliminar el enfermero
            $enfermero->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Enfermero/a eliminado exitosamente.',
                'duration' => 3000
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el enfermero/a: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit enfermeros');
        
        try {
            $enfermero = Enfermero::findOrFail($id);
            $nombreEnfermero = $enfermero->nombres . ' ' . $enfermero->apellidos;
            $nuevoEstado = !$enfermero->status;
            
            // Actualizar el estado del enfermero
            $enfermero->update(['status' => $nuevoEstado]);
            
            // Actualizar el estado del usuario relacionado si existe
            if ($enfermero->user) {
                $enfermero->user->update(['status' => $nuevoEstado]);
            }
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Estado de '{$nombreEnfermero}' actualizado a " . ($nuevoEstado ? 'Activo' : 'Inactivo'),
                'duration' => 4000
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function getEnfermerosProperty()
    {
        return Enfermero::with(['user', 'empresa', 'sucursal'])
            ->forUser()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombres', 'like', '%' . $this->search . '%')
                      ->orWhere('apellidos', 'like', '%' . $this->search . '%')
                      ->orWhere('documento_identidad', 'like', '%' . $this->search . '%')
                      ->orWhere('licencia_enfermeria', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q) {
                          $q->where('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->when($this->sucursal_id, function ($query) {
                $query->where('sucursal_id', $this->sucursal_id);
            })
            ->when($this->nivel_experiencia, function ($query) {
                $query->where('nivel_experiencia', $this->nivel_experiencia);
            })
            ->when($this->tipo_enfermero, function ($query) {
                $query->where('tipo_enfermero', $this->tipo_enfermero);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $query = Enfermero::forUser();
        
        return [
            'total' => $query->count(),
            'activos' => $query->where('status', true)->count(),
            'inactivos' => $query->where('status', false)->count(),
            'promedio_experiencia' => round($query->avg('anios_experiencia') ?? 0, 1),
        ];
    }

    public function getEmpresasProperty()
    {
        return Empresa::forUser()->get();
    }

    public function getSucursalesProperty()
    {
        return Sucursal::when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->forUser()
            ->get();
    
    }

    public function enviarMensajeBienvenida($id)
    {

        $this->authorize('edit enfermeros');
        
        try {
            $enfermero = Enfermero::find($id);
            $user = $enfermero->user;
            
            if (!$user) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'El enfermero no tiene usuario asociado.',
                    'duration' => 3000
                ]);
                return;
            }
            
            if (empty($enfermero->telefono)) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'El enfermero no tiene teléfono registrado.',
                    'duration' => 3000
                ]);
                return;
            }
            
            // Usar el servicio universal de notificaciones
            $notificationService = new UniversalNotificationService();
            
            // Enviar mensaje de bienvenida
            $resultado = $notificationService->sendNurseWelcomeMessage($enfermero);
            
            if ($resultado) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Mensaje de bienvenida enviado exitosamente por WhatsApp.',
                    'duration' => 3000
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se pudo enviar el mensaje de bienvenida. Verifique la configuración de notificaciones.',
                    'duration' => 3000
                ]);
            }
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al enviar mensaje: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }
    
    private function crearMensajeBienvenida($user, $enfermero, $plainPassword = null)
    {
        $empresa = $user->empresa;
        $sucursal = $user->sucursal;
        $passwordToShow = $plainPassword ?: $enfermero->documento_identidad;
        
        // Mensaje de bienvenida personalizado
        $mensaje = "🏥 ¡Bienvenido/a {$enfermero->nombres} {$enfermero->apellidos}!\n\n";
        $mensaje .= "✅ Su cuenta ha sido creada exitosamente en nuestro sistema de salud.\n\n";
        $mensaje .= "📋 *Datos de acceso:*\n";
        $mensaje .= "• Usuario: {$user->username}\n";
        $mensaje .= "• Email: {$user->email}\n";
        $mensaje .= "• Contraseña temporal: {$passwordToShow}\n\n";
        $mensaje .= "🏥 *Información institucional:*\n";
        $mensaje .= "• Empresa: {$empresa->razon_social}\n";
        $mensaje .= "• Sucursal: {$sucursal->nombre}\n";
        $mensaje .= "• Tipo de enfermero: {$enfermero->tipo_enfermero}\n";
        
        // Obtener especialidades
        $especialidades = $enfermero->especialidades()->pluck('nombre')->toArray();
        if (count($especialidades) > 0) {
            $mensaje .= "• Especialidades: " . implode(', ', $especialidades) . "\n";
        }
        $mensaje .= "\n🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n";
        $mensaje .= "📱 ¿Preguntas? Contáctenos al {$empresa->telefono}\n\n";
        $mensaje .= "¡Gracias por formar parte de nuestro equipo de enfermería! 🏥💉";
        
        return $mensaje;
    }
    
    private function formatearTelefono($telefono)
    {
        // Eliminar espacios y caracteres no numéricos
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        
        // Obtener el país de la empresa del usuario logueado
        $pais = null;
        if (auth()->user() && auth()->user()->empresa) {
            $pais = auth()->user()->empresa->pais;
        }
        
        // Si no hay teléfono limpio, retornar vacío
        if (empty($telefono)) {
            return '';
        }
        
        // Agregar código del país si está disponible
        if ($pais && $pais->codigo_telefonico) {
            // Verificar si el teléfono ya incluye el código del país
            if (!str_starts_with($telefono, $pais->codigo_telefonico)) {
                $telefono = $pais->codigo_telefonico . $telefono;
            } else {
                $telefono = $telefono;
            }
        } else {
            // Si no hay código de país, intentar detectar por longitud (fallback)
            if (strlen($telefono) === 9 && $telefono[0] === '9') {
                $telefono = '+51' . $telefono; // Perú por defecto
            } elseif (strlen($telefono) === 10) {
                $telefono = '+52' . $telefono; // México por defecto
            }
        }
        
        return $telefono;
    }

    public function render()
    {
        return view('livewire.admin.enfermeros.index', [
            'enfermeros' => $this->enfermeros,
            'stats' => $this->stats,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}