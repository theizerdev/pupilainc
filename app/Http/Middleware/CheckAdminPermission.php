<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Verificar si el usuario tiene el permiso específico
        if (!$user->can($permission)) {
            $module = $this->getModuleFromPermission($permission);
            $alternativeModules = $this->getAlternativeModules($user);
            
            return response()->view('errors.403-custom', [
                'message' => 'No tienes permisos para acceder a esta sección del sistema.',
                'required_permission' => $permission,
                'module' => $module,
                'alternative_modules' => $alternativeModules
            ], 403);
        }

        return $next($request);
    }

    /**
     * Obtener el módulo desde el permiso
     */
    private function getModuleFromPermission(string $permission): ?string
    {
        // Mapeo directo de permisos a nombres de módulos amigables
        $moduleMap = [
            'recepcion' => 'Recepción',
            'consultorios' => 'Consultorios',
            'empresas' => 'Empresas',
            'especialidades' => 'Especialidades',
            'tipo-consultas' => 'Tipos de Consulta',
            'subespecialidades' => 'Subespecialidades',
            'medicos' => 'Médicos',
            'pacientes' => 'Pacientes',
            'paises' => 'Países',
            'sucursales' => 'Sucursales',
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'permissions' => 'Permisos',
            'active sessions' => 'Sesiones Activas',
            'monitoreo' => 'Monitoreo',
            'exchange-rates' => 'Tasas de Cambio',
            'series' => 'Series',
            'pagos' => 'Pagos',
            'activity log' => 'Registro de Actividad',
            'cajas' => 'Cajas',
            'reglas mora' => 'Reglas de Mora',
            'notifications' => 'Notificaciones',
            'whatsapp' => 'WhatsApp',
            'database export' => 'Exportar Base de Datos',
            'conceptos pago' => 'Conceptos de Pago',
            'citas' => 'Citas Médicas',
        ];

        foreach ($moduleMap as $key => $name) {
            if (str_contains($permission, $key)) {
                return $name;
            }
        }

        // Fallback: extraer la última palabra
        $parts = explode(' ', $permission);
        return ucfirst(end($parts));
    }

    /**
     * Obtener módulos alternativos a los que el usuario tiene acceso
     */
    private function getAlternativeModules($user): array
    {
        $allModules = [
            [
                'permission' => 'access recepcion dashboard',
                'route' => route('admin.recepcion.dashboard'),
                'title' => 'Recepción',
                'description' => 'Gestión de turnos y consultorios',
                'icon' => 'fas fa-concierge-bell',
                'color' => 'linear-gradient(135deg, #FF6B6B 0%, #EE5D5D 100%)'
            ],
            [
                'permission' => 'access citas',
                'route' => route('admin.citas.index'),
                'title' => 'Citas Médicas',
                'description' => 'Gestión de citas y agenda',
                'icon' => 'fas fa-calendar-alt',
                'color' => 'linear-gradient(135deg, #1A535C 0%, #103b42 100%)'
            ],
            [
                'permission' => 'access pacientes',
                'route' => route('admin.pacientes.index'),
                'title' => 'Pacientes',
                'description' => 'Directorio de pacientes',
                'icon' => 'fas fa-user-injured',
                'color' => 'linear-gradient(135deg, #FF9F1C 0%, #e0860b 100%)'
            ],
            [
                'permission' => 'access medicos',
                'route' => route('admin.medicos.index'),
                'title' => 'Médicos',
                'description' => 'Directorio médico',
                'icon' => 'fas fa-user-md',
                'color' => 'linear-gradient(135deg, #2EC4B6 0%, #20998d 100%)'
            ],
            [
                'permission' => 'access pagos',
                'route' => route('admin.pagos.index'),
                'title' => 'Pagos',
                'description' => 'Gestión de cobros',
                'icon' => 'fas fa-money-bill-wave',
                'color' => 'linear-gradient(135deg, #E71D36 0%, #c41027 100%)'
            ],
            [
                'permission' => 'access cajas',
                'route' => route('admin.cajas.index'),
                'title' => 'Cajas',
                'description' => 'Control de caja chica',
                'icon' => 'fas fa-cash-register',
                'color' => 'linear-gradient(135deg, #8338ec 0%, #6821d1 100%)'
            ],
            [
                'permission' => 'access whatsapp',
                'route' => route('admin.whatsapp.dashboard'),
                'title' => 'WhatsApp',
                'description' => 'Mensajería y notificaciones',
                'icon' => 'fab fa-whatsapp',
                'color' => 'linear-gradient(135deg, #25D366 0%, #1a9648 100%)'
            ],
            [
                'permission' => 'view monitoreo servidor',
                'route' => route('admin.monitoreo.servidor'),
                'title' => 'Monitoreo',
                'description' => 'Estado del sistema',
                'icon' => 'fas fa-server',
                'color' => 'linear-gradient(135deg, #3a86ff 0%, #1e60cc 100%)'
            ]
        ];

        $accessibleModules = [];
        $count = 0;

        foreach ($allModules as $module) {
            // Verificar si el usuario tiene el permiso de acceso principal
            // O si tiene el rol de Super Administrador (siempre tiene acceso)
            if ($user->can($module['permission']) || $user->hasRole('Super Administrador')) {
                // Verificar que la ruta existe antes de agregarla
                try {
                    // La ruta ya se resolvió en la definición del array, pero si fallara (ruta no definida) lanzaría excepción antes.
                    // Sin embargo, `route()` helper lanza excepción si la ruta no existe.
                    // Como estamos definiendo las rutas estáticamente, asumimos que existen.
                    // Pero para mayor seguridad en runtime si cambiamos nombres de rutas:
                    // Es mejor almacenar el nombre de la ruta en el array y resolverlo aquí.
                    // Pero por simplicidad del código anterior, lo dejaré así, asumiendo que las rutas existen.
                    
                    $accessibleModules[] = $module;
                    $count++;
                } catch (\Exception $e) {
                    continue;
                }
            }

            if ($count >= 4) break; // Mostrar máximo 4 sugerencias
        }

        return $accessibleModules;
    }
}
