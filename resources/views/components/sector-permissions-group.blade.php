<div class="sector-permissions-group">
    @php
        $sectors = getPermissionSectors();
        $permissionsBySector = $permissions->groupBy('sector');
    @endphp

    @foreach($sectors as $sectorKey => $sector)
        @php
            $sectorPermissions = $permissionsBySector->get($sectorKey, collect());
            $hasPermissions = $sectorPermissions->isNotEmpty();
        @endphp

        @if($hasPermissions || $showEmpty)
            <div class="mb-6 sector-sector-{{ $sectorKey }}">
                <div class="flex items-center gap-3 mb-3 p-3 rounded-lg"
                     style="background-color: {{ getSectorColor($sectorKey) }}10;">
                    <span class="text-xl">{{ getSectorIcon($sectorKey) }}</span>
                    <div>
                        <h4 class="font-semibold text-gray-800 dark:text-white">
                            {{ $sector['name'] }}
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $sector['description'] }}
                        </p>
                    </div>
                    @if($hasPermissions)
                        <div class="ml-auto">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                  style="background-color: {{ getSectorColor($sectorKey) }}20; color: {{ getSectorColor($sectorKey) }};">
                                {{ $sectorPermissions->count() }} permisos
                            </span>
                        </div>
                    @endif
                </div>

                @if($hasPermissions)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($sectorPermissions as $permission)
                            <label class="flex items-center p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="{{ $permission->id }}"
                                       {{ in_array($permission->id, $selectedPermissions ?? []) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-{{ getSectorColor($sectorKey) }}-600 shadow-sm focus:border-{{ getSectorColor($sectorKey) }}-300 focus:ring focus:ring-{{ getSectorColor($sectorKey) }}-200 focus:ring-opacity-50">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $permission->name }}
                                    </span>
                                    @if($permission->module)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $permission->module }}
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                        <p>No hay permisos disponibles en este sector</p>
                    </div>
                @endif
            </div>
        @endif
    @endforeach

    <!-- Permisos sin sector -->
    @php
        $unassignedPermissions = $permissionsBySector->get(null, collect());
    @endphp

    @if($unassignedPermissions->isNotEmpty() && $showUnassigned)
        <div class="mb-6 sector-unassigned">
            <div class="flex items-center gap-3 mb-3 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
                <span class="text-xl">⚠️</span>
                <div>
                    <h4 class="font-semibold text-yellow-800 dark:text-yellow-300">
                        Permisos sin asignar
                    </h4>
                    <p class="text-sm text-yellow-600 dark:text-yellow-400">
                        Estos permisos no pertenecen a ningún sector
                    </p>
                </div>
                <div class="ml-auto">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-200 text-yellow-800">
                        {{ $unassignedPermissions->count() }} permisos
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($unassignedPermissions as $permission)
                    <label class="flex items-center p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="checkbox" 
                               name="permissions[]" 
                               value="{{ $permission->id }}"
                               {{ in_array($permission->id, $selectedPermissions ?? []) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-gray-600 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                        <div class="ml-3">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $permission->name }}
                            </span>
                            @if($permission->module)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $permission->module }}
                                </div>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .sector-permissions-group input[type="checkbox"]:checked {
        background-color: currentColor;
    }
</style>
@endpush