<div>
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                Gestión de Permisos por Sectores
            </h2>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Estadísticas rápidas -->
                @foreach($sectorStats as $sectorKey => $stats)
                    <div class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <span class="text-lg">{{ getSectorIcon($sectorKey) }}</span>
                        <div class="text-xs">
                            <div class="font-semibold">{{ $stats['name'] }}</div>
                            <div class="text-gray-500">{{ $stats['total_permissions'] }} permisos</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Búsqueda -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Buscar permisos
                    </label>
                    <input type="text" 
                           wire:model.live="search" 
                           placeholder="Buscar por nombre, módulo o sector..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Filtro por sector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Sector
                    </label>
                    <select wire:model.live="selectedSector" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">Todos los sectores</option>
                        @foreach($sectors as $sectorKey => $sector)
                            <option value="{{ $sectorKey }}">{{ $sector['name'] }}</option>
                        @endforeach
                        <option value="unassigned">Sin asignar</option>
                    </select>
                </div>

                <!-- Filtro por rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Rol
                    </label>
                    <select wire:model.live="selectedRole" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Acciones -->
                <div class="flex items-end">
                    <div class="flex gap-2">
                        <button wire:click="autoAssignSectors" 
                                class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm">
                            Auto-asignar
                        </button>
                        <button wire:click="resetFilters" 
                                class="px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-sm">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Opciones adicionales -->
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" wire:model.live="showOnlyUnassigned" 
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Mostrar solo permisos sin asignar
                    </span>
                </label>
            </div>
        </div>

        <!-- Tabla de permisos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Permiso
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Módulo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Sector
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Roles
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($permissions as $permission)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $permission->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        ID: {{ $permission->id }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $permission->module ?? 'Sin módulo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($permission->sector)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              style="background-color: {{ getSectorColor($permission->sector) }}20; color: {{ getSectorColor($permission->sector) }};">
                                            {{ getSectorIcon($permission->sector) }} {{ formatSectorName($permission->sector, false) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            ⚠️ Sin asignar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($permission->roles as $role)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Sin roles</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex gap-2">
                                        @if($permission->sector)
                                            <!-- Cambiar sector -->
                                            <div class="relative group">
                                                <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                    </svg>
                                                </button>
                                                <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                                    <div class="py-1">
                                                        @foreach($sectors as $sectorKey => $sector)
                                                            <button wire:click="assignToSector({{ $permission->id }}, '{{ $sectorKey }}')"
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                {{ $sector['name'] }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Desasignar -->
                                            <button wire:click="unassignFromSector({{ $permission->id }})"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    onclick="return confirm('¿Deseas desasignar este permiso del sector?')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <!-- Asignar -->
                                            <div class="relative group">
                                                <button class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </button>
                                                <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                                    <div class="py-1">
                                                        @foreach($sectors as $sectorKey => $sector)
                                                            <button wire:click="assignToSector({{ $permission->id }}, '{{ $sectorKey }}')"
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                {{ $sector['name'] }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    @if($showOnlyUnassigned)
                                        No hay permisos sin asignar
                                    @elseif($search)
                                        No se encontraron permisos que coincidan con "{{ $search }}"
                                    @elseif($selectedSector !== 'all')
                                        No hay permisos en el sector seleccionado
                                    @else
                                        No hay permisos para mostrar
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($permissions->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                    {{ $permissions->links('livewire.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Notificaciones -->
    <div x-data x-on:notify.window="$dispatch('toast', { message: $event.detail.message, type: $event.detail.type })">
    </div>
</div>