<?php

namespace App\Livewire\Admin\Enfermeros;

use App\Models\Enfermero;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;
use App\Services\WhatsAppService;

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
        $this->authorize('admin.enfermeros.destroy');
        
        try {
            $enfermero = Enfermero::findOrFail($id);
            $enfermero->delete();
            
            session()->flash('success', 'Enfermero/a eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el enfermero/a: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit enfermeros');
        
        try {
            $enfermero = Enfermero::findOrFail($id);
            $nuevoEstado = !$enfermero->status;
            
            // Actualizar el estado del enfermero
            $enfermero->update(['status' => $nuevoEstado]);
            
            // Actualizar el estado del usuario relacionado si existe
            if ($enfermero->user) {
                $enfermero->user->update(['status' => $nuevoEstado]);
            }
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Estado del enfermero/a actualizado exitosamente.',
                'duration' => 3000
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
            $enfermero = Enfermero::findOrFail($id);
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
            
            // Generar contraseña temporal (usando el documento de identidad)
            $plainPassword = $enfermero->documento_identidad;
            
            // Crear y enviar mensaje de bienvenida
            $mensaje = $this->crearMensajeBienvenida($user, $enfermero, $plainPassword);
            $telefono = $this->formatearTelefono($enfermero->telefono);
            
            $whatsAppService = new WhatsAppService($user->empresa_id);
            
            if ($whatsAppService->isConfigured()) {
                $resultado = $whatsAppService->sendMessage($telefono, $mensaje, true);
                
                if ($resultado) {
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'Mensaje de bienvenida enviado exitosamente por WhatsApp.',
                        'duration' => 3000
                    ]);
                } else {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'No se pudo enviar el mensaje de WhatsApp. Verifique la configuración.',
                        'duration' => 3000
                    ]);
                }
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'WhatsApp no está configurado para esta empresa.',
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
        $especialidades = $enfermero->especialidades()->pluck('especialidad')->toArray();
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
        
        // Si es un número peruano (9 dígitos y empieza con 9), agregar +51
        if (strlen($telefono) === 9 && $telefono[0] === '9') {
            $telefono = '+51' . $telefono;
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