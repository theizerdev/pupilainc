<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Traits\Exportable;
use App\Traits\HasDynamicLayout;
use App\Services\UniversalNotificationService;

class Index extends Component
{
    use WithPagination, Exportable, HasDynamicLayout;

    public $search = '';
    public $status = '';
    public $empresa_id = '';
    public $sucursal_id = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10]
    ];

    public function mount()
    {
        // Verificar permiso para ver usuarios
        if (!Auth::user()->can('access users')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingEmpresaId()
    {
        $this->resetPage();
    }

    public function updatingSucursalId()
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

    protected function getExportQuery()
    {
        return $this->getBaseQuery();
    }

    protected function getExportHeaders(): array
    {
        return ['ID', 'Nombre', 'Email', 'Empresa', 'Sucursal', 'Rol', 'Status'];
    }

    protected function formatExportRow($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->empresa->razon_social ?? 'N/A',
            $user->sucursal->nombre ?? 'N/A',
            $user->roles->pluck('name')->join(', '),
            $user->status ? 'Activo' : 'Inactivo'
        ];
    }

    private function getBaseQuery()
    {
        return User::forUser()->with(['empresa', 'sucursal', 'roles'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->when($this->sucursal_id, function ($query) {
                $query->where('sucursal_id', $this->sucursal_id);
            });
    }

    public function render()
    {
        $users = $this->getBaseQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        $empresas = Empresa::forUser()->where('status', 'active')->get();
        $sucursales = Sucursal::forUser()->where('status', 'active')
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->get();

        $roles = Role::all();

        // Calcular estadísticas
        $totalUsers = User::forUser()->count();
        $activeUsers = User::forUser()->where('status', 1)->count();
        $pendingUsers = 0;
        $inactiveUsers = User::forUser()->where('status', 0)->count();

        return $this->renderWithLayout('livewire.admin.users.index', compact('users', 'empresas', 'sucursales', 'roles', 'totalUsers', 'activeUsers', 'pendingUsers', 'inactiveUsers'), [
            'title' => 'Lista de Usuarios',
            'breadcrumb' => [
                'admin.dashboard' => 'Dashboard',
                'admin.users.index' => 'Usuarios'
            ]
        ]);
    }

    public function toggleStatus(User $user)
    {
        if (!Auth::user()->can('edit users')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para editar usuarios.',
                'duration' => 4000
            ]);
            return;
        }

        if ($user->id === Auth::id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No puedes desactivar tu propia cuenta.',
                'duration' => 4000
            ]);
            return;
        }

        $previousStatus = $user->status;
        $user->status = !$user->status;
        $user->save();

        // Enviar notificación por WhatsApp si el usuario tiene teléfono
        if ($user->phone || $user->medico || $user->enfermero || $user->paciente) {
            try {
                $notificationService = new UniversalNotificationService();
                
                // Enviar notificación de cambio de estado
                $notificationService->sendStatusChangeMessage($user, $user->status ? 'active' : 'inactive');
            } catch (\Exception $e) {
                \Log::error('Error enviando notificación WhatsApp de cambio de estado: ' . $e->getMessage());
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Estado de usuario '{$user->name}' actualizado a " . ($user->status ? 'activo' : 'inactivo') . ".",
            'duration' => 4000
        ]);
    }

    public function unlockUser(User $user)
    {
        if (!Auth::user()->can('edit users')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para desbloquear usuarios.',
                'duration' => 4000
            ]);
            return;
        }

        $user->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
            'status' => true // Reactivar usuario al desbloquear
        ]);

        // Enviar notificación por WhatsApp si el usuario tiene teléfono
        if ($user->phone || $user->medico || $user->enfermero || $user->paciente) {
            try {
                $notificationService = new UniversalNotificationService();
                
                // Enviar mensaje de bienvenida o reactivación
                $mensaje = "Hola {$user->name}, tu cuenta ha sido desbloqueada exitosamente por el administrador. Ya puedes acceder al sistema nuevamente.";
                
                $notificationService->sendGenericNotification(
                    $user->empresa_id,
                    'usuarios',
                    'cuenta_desbloqueada',
                    'usuario',
                    $user,
                    $mensaje
                );
            } catch (\Exception $e) {
                \Log::error('Error enviando notificación WhatsApp de desbloqueo: ' . $e->getMessage());
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Usuario '{$user->name}' desbloqueado exitosamente.",
            'duration' => 4000
        ]);
    }

    public function delete(User $user)
    {
        // Verificar permiso para eliminar usuarios
        if (!Auth::user()->can('delete users')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para eliminar usuarios.',
                'duration' => 4000
            ]);
            return;
        }

        // No permitir eliminar al usuario actual
        if ($user->id === Auth::id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No puedes eliminar tu propia cuenta.',
                'duration' => 4000
            ]);
            return;
        }

        try {
            // Verificar si el usuario tiene registros relacionados que impidan la eliminación
            $relatedRecords = $this->checkRelatedRecords($user);
            
            if (!empty($relatedRecords)) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => "No se puede eliminar el usuario '{$user->name}' porque tiene registros relacionados: " . implode(', ', $relatedRecords) . '. Considera desactivar el usuario en lugar de eliminarlo.',
                    'duration' => 6000
                ]);
                return;
            }

            $nombreUsuario = $user->name;
            
            // Eliminar el usuario (las relaciones en cascada se manejarán automáticamente)
            $user->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Usuario '{$nombreUsuario}' eliminado exitosamente.",
                'duration' => 4000
            ]);
            $this->resetPage();
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar errores de clave foránea
            if ($e->getCode() === '23000') {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => "No se puede eliminar el usuario '{$user->name}' porque tiene registros relacionados en el sistema. Por seguridad, solo se pueden eliminar usuarios sin datos asociados. Considera desactivar el usuario en lugar de eliminarlo.",
                    'duration' => 6000
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => "Error al eliminar el usuario: " . $e->getMessage(),
                    'duration' => 6000
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => "Error inesperado al eliminar el usuario: " . $e->getMessage(),
                'duration' => 6000
            ]);
        }
    }

    /**
     * Verifica si el usuario tiene registros relacionados que impidan su eliminación
     */
    private function checkRelatedRecords(User $user): array
    {
        $relatedRecords = [];
        
        // Verificar si el usuario tiene un perfil de médico
        if ($user->medico && $user->medico->citas()->exists()) {
            $relatedRecords[] = 'citas médicas';
        }
        
        // Verificar si el usuario tiene un perfil de enfermero
        if (method_exists($user, 'enfermero') && $user->enfermero) {
            $relatedRecords[] = 'registro de enfermería';
        }
        
        // Verificar otros registros relacionados según sea necesario
        
        return $relatedRecords;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->empresa_id = '';
        $this->sucursal_id = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function loadSucursales()
    {
        // Este método se llama cuando se cambia la empresa en los filtros
        $this->sucursal_id = '';
    }
}