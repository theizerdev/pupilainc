<div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Crear Nuevo Rol</h5>
                    <p class="text-muted mb-0">Define un nuevo rol y asigna los permisos por sector</p>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">Nombre del Rol</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   wire:model="name"
                                   placeholder="Ej. editor, supervisor, etc.">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">El nombre debe ser único y descriptivo</div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0 fw-bold">Permisos del Rol</h6>
                                    <p class="text-muted mb-0">Selecciona los permisos organizados por sector</p>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-3">{{ count($selectedPermissions) }} permisos seleccionados</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="selectAll"
                                               wire:click="toggleSelectAll"
                                               @if($selectAll) checked @endif>
                                        <label class="form-check-label fw-bold" for="selectAll">
                                            Seleccionar todos
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @if(count($sectorPermissions) > 0)
                                @php
                                    $sectorColors = [
                                        'blue' => ['bg' => 'bg-primary', 'text' => 'text-primary', 'label' => 'bg-label-primary', 'border' => 'border-primary'],
                                        'green' => ['bg' => 'bg-success', 'text' => 'text-success', 'label' => 'bg-label-success', 'border' => 'border-success'],
                                        'purple' => ['bg' => 'bg-info', 'text' => 'text-info', 'label' => 'bg-label-info', 'border' => 'border-info'],
                                        'orange' => ['bg' => 'bg-warning', 'text' => 'text-warning', 'label' => 'bg-label-warning', 'border' => 'border-warning'],
                                        'teal' => ['bg' => 'bg-success', 'text' => 'text-success', 'label' => 'bg-label-success', 'border' => 'border-success'],
                                        'gray' => ['bg' => 'bg-secondary', 'text' => 'text-secondary', 'label' => 'bg-label-secondary', 'border' => 'border-secondary'],
                                    ];
                                @endphp

                                <ul class="nav nav-pills nav-fill flex-column flex-sm-row mb-4" role="tablist">
                                    @foreach($sectorPermissions as $sectorKey => $modules)
                                        @php
                                            $sectorInfo = $sectors[$sectorKey] ?? ['name' => ucfirst($sectorKey), 'color' => 'gray'];
                                            $colors = $sectorColors[$sectorInfo['color']] ?? $sectorColors['gray'];
                                            $sectorTotal = 0;
                                            $sectorSelected = 0;
                                            foreach ($modules as $modPerms) {
                                                $sectorTotal += count($modPerms);
                                                foreach ($modPerms as $p) {
                                                    if (in_array($p->id, $selectedPermissions)) $sectorSelected++;
                                                }
                                            }
                                        @endphp
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $activeSector === $sectorKey ? 'active' : '' }}"
                                                    wire:click="setActiveSector('{{ $sectorKey }}')"
                                                    type="button">
                                                {{ $sectorInfo['name'] }}
                                                <span class="badge {{ $activeSector === $sectorKey ? 'bg-white text-primary' : $colors['bg'] }} ms-1">{{ $sectorSelected }}/{{ $sectorTotal }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                @foreach($sectorPermissions as $sectorKey => $modules)
                                    <div class="{{ $activeSector === $sectorKey ? '' : 'd-none' }}">
                                        @php
                                            $sectorInfo = $sectors[$sectorKey] ?? ['name' => ucfirst($sectorKey), 'color' => 'gray', 'description' => ''];
                                            $colors = $sectorColors[$sectorInfo['color']] ?? $sectorColors['gray'];
                                        @endphp

                                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded {{ $colors['label'] }}">
                                            <div>
                                                <h6 class="mb-0 {{ $colors['text'] }}">{{ $sectorInfo['name'] }}</h6>
                                                <small class="text-muted">{{ $sectorInfo['description'] ?? '' }}</small>
                                            </div>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="toggleSector{{ $sectorKey }}"
                                                       wire:click="toggleSectorPermissions('{{ $sectorKey }}')"
                                                       {{ ($sectorStates[$sectorKey] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="toggleSector{{ $sectorKey }}">
                                                    Todo el sector
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            @foreach($modules as $module => $permissions)
                                                @php
                                                    $moduleKey = $sectorKey . '.' . $module;
                                                    $moduleSelectedCount = 0;
                                                    foreach ($permissions as $permission) {
                                                        if (in_array($permission->id, $selectedPermissions)) {
                                                            $moduleSelectedCount++;
                                                        }
                                                    }
                                                @endphp
                                                <div class="col-xl-4 col-md-6 mb-4">
                                                    <div class="card h-100 border {{ $colors['border'] }}">
                                                        <div class="card-header {{ $colors['label'] }}">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <h6 class="mb-0">{{ ucfirst($module) }}</h6>
                                                                <span class="badge {{ $colors['bg'] }}">{{ $moduleSelectedCount }}/{{ count($permissions) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input"
                                                                       type="checkbox"
                                                                       id="toggleModule{{ $sectorKey }}{{ $module }}"
                                                                       wire:click="toggleModulePermissions('{{ $sectorKey }}', '{{ $module }}')"
                                                                       {{ ($moduleStates[$moduleKey] ?? false) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="toggleModule{{ $sectorKey }}{{ $module }}">
                                                                    Seleccionar todos
                                                                </label>
                                                            </div>

                                                            <div class="permission-list" style="max-height: 200px; overflow-y: auto;">
                                                                @foreach($permissions as $permission)
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input permission-checkbox"
                                                                               type="checkbox"
                                                                               id="permission{{ $permission->id }}"
                                                                               value="{{ $permission->id }}"
                                                                               wire:model.live="selectedPermissions">
                                                                        <label class="form-check-label" for="permission{{ $permission->id }}">
                                                                            {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="ri ri-information-line"></i> No hay permisos disponibles.
                                </div>
                            @endif

                            @error('selectedPermissions')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-label-secondary">
                                <i class="ri ri-arrow-left-line"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary" @cannot('create roles') disabled @endcannot>
                                <i class="ri ri-save-line"></i> Crear Rol
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .permission-list::-webkit-scrollbar { width: 6px; }
        .permission-list::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .permission-list::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
        .permission-list::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
        .nav-pills .nav-link { cursor: pointer; }
    </style>
</div>
