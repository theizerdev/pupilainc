<div class="sector-dashboard">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        @foreach(getSectorStats() as $sectorKey => $stats)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg"
                             style="background-color: {{ getSectorColor($sectorKey) }}20;">
                            {{ getSectorIcon($sectorKey) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-white text-sm">
                                {{ formatSectorName($sectorKey, false) }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $stats['description'] }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Permisos</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white">
                            {{ $stats['total_permissions'] }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Roles</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white">
                            {{ $stats['total_roles'] }}
                        </span>
                    </div>
                    
                    @if(hasSectorAccess(auth()->user(), $sectorKey))
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                ✓ Tienes acceso
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
<style>
    .sector-dashboard .bg-white:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease-in-out;
    }
</style>
@endpush