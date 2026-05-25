<div>
    @section('title', 'Personalización de Plantilla')

    @push('styles')
    <style>
        .template-hero { background: linear-gradient(135deg, #06B6D4 0%, #0891B2 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .template-hero h2 { color:#fff; margin:0; }
        .template-hero p { opacity:.9; margin:0; }

        .config-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; transition:all .2s; background:#fff; height:100%; }
        .config-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .config-card .card-header { background:transparent; border-bottom:1px solid rgba(0,0,0,.06); padding:1rem 1.25rem; }
        .config-card .card-body { padding:1.25rem; }

        .preview-container { border:1px solid #e5e7eb; border-radius:8px; padding:20px; transition:all .3s; }
        .color-picker-wrapper { position:relative; display:inline-block; }
        .color-picker-wrapper input[type="color"] { width:100%; height:42px; border:2px solid #e5e7eb; border-radius:8px; cursor:pointer; padding:2px; }
        .color-picker-wrapper input[type="color"]:hover { border-color:#7367F0; }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Personalización</li>
        </ol>
    </nav>

    {{-- Hero Section --}}
    <div class="template-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="fw-semibold"><i class="ri ri-palette-line me-2"></i>Personalización de Plantilla</h2>
            <p class="mt-1">Configura la apariencia y diseño del sistema</p>
        </div>
        <button type="button" class="btn btn-light btn-sm" wire:click="resetToDefaults" wire:confirm="¿Restaurar valores por defecto?">
            <i class="ri ri-refresh-line me-1"></i>Restaurar Valores
        </button>
    </div>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="ri ri-checkbox-circle-line me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Configuration Cards --}}
    <div class="row g-4 mb-4">
        <!-- Colores y Tema -->
        <div class="col-md-6 col-xl-3">
            <div class="config-card card mb-0">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold"><i class="ri ri-palette-line me-2 text-primary"></i>Colores y Tema</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Color Primario</label>
                        <div class="color-picker-wrapper">
                            <input type="color" class="form-control form-control-color" wire:model.live="primary_color" title="Seleccionar color">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Tema</label>
                        <select class="form-select form-select-sm" wire:model.live="theme">
                            <option value="light">☀️ Claro</option>
                            <option value="dark">🌙 Oscuro</option>
                            <option value="system">💻 Sistema</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Estilo</label>
                        <select class="form-select form-select-sm" wire:model.live="skin">
                            <option value="0">Por Defecto</option>
                            <option value="1">Con Bordes</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model.live="semi_dark" id="semi_dark">
                        <label class="form-check-label small" for="semi_dark">Menú Semi-Oscuro</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout -->
        <div class="col-md-6 col-xl-3">
            <div class="config-card card mb-0">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold"><i class="ri ri-layout-masonry-line me-2 text-success"></i>Diseño</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Tipo de Layout</label>
                        <select class="form-select form-select-sm" wire:model.live="layout_type">
                            <option value="vertical">↕️ Vertical</option>
                            <option value="horizontal">↔️ Horizontal</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Ancho de Contenido</label>
                        <select class="form-select form-select-sm" wire:model.live="content_layout">
                            <option value="compact">Compacto</option>
                            <option value="wide">Ancho</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Dirección de Texto</label>
                        <select class="form-select form-select-sm" wire:model.live="text_direction">
                            <option value="ltr">← Izquierda a Derecha</option>
                            <option value="rtl">→ Derecha a Izquierda</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menú y Navegación -->
        <div class="col-md-6 col-xl-3">
            <div class="config-card card mb-0">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold"><i class="ri ri-menu-line me-2 text-warning"></i>Menú y Navegación</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Tipo de Navbar</label>
                        <select class="form-select form-select-sm" wire:model.live="navbar_type">
                            <option value="sticky">📌 Pegajoso</option>
                            <option value="static">⏺️ Estático</option>
                            <option value="hidden">👁️ Oculto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2">Tipo de Header</label>
                        <select class="form-select form-select-sm" wire:model.live="header_type">
                            <option value="static">Estático</option>
                            <option value="fixed">Fijo</option>
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" wire:model.live="menu_collapsed" id="menu_collapsed">
                        <label class="form-check-label small" for="menu_collapsed">Menú Colapsado</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" wire:model.live="footer_fixed" id="footer_fixed">
                        <label class="form-check-label small" for="footer_fixed">Footer Fijo</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model.live="dropdown_on_hover" id="dropdown_on_hover">
                        <label class="form-check-label small" for="dropdown_on_hover">Dropdown al Pasar Mouse</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista Previa -->
        <div class="col-md-6 col-xl-3">
            <div class="config-card card mb-0">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold"><i class="ri ri-eye-line me-2 text-info"></i>Vista Previa</h6>
                </div>
                <div class="card-body">
                    <div class="preview-container" style="background: {{ $theme === 'dark' ? '#2b2c40' : '#f8f9fa' }};">
                        <div style="background: {{ $primary_color }}; height: 35px; border-radius: 4px; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600;">
                            Header ({{ ucfirst($header_type) }})
                        </div>
                        <div class="d-flex" style="height: 80px;">
                            @if($layout_type === 'vertical')
                                <div style="width: {{ $menu_collapsed ? '50px' : '160px' }}; background: {{ $semi_dark ? '#3a3e5c' : '#e9ecef' }}; border-radius: 4px; margin-right: 8px; display: flex; align-items: center; justify-content: center; font-size: 10px; writing-mode: vertical-lr; text-orientation: mixed;">
                                    Menú {{ $menu_collapsed ? 'Mini' : 'Completo' }}
                                </div>
                            @endif
                            <div style="flex: 1; background: {{ $theme === 'dark' ? '#25293c' : '#ffffff' }}; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: {{ $theme === 'dark' ? '#fff' : '#6c757d' }};">
                                Contenido
                            </div>
                        </div>
                        @if($layout_type === 'horizontal')
                            <div style="background: {{ $semi_dark ? '#3a3e5c' : '#dee2e6' }}; height: 25px; border-radius: 4px; margin-top: 8px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: {{ $semi_dark ? '#fff' : '#6c757d' }};">
                                Menú Horizontal
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" wire:click="$refresh">
            <i class="ri ri-refresh-line me-1"></i>Actualizar Vista
        </button>
        <button type="button" class="btn btn-primary px-4" wire:click="save">
            <i class="ri ri-save-line me-1"></i>Guardar Configuración
        </button>
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('template-updated', (event) => {
        try {
            // Recargar página inmediatamente para aplicar cambios
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } catch (error) {
            console.warn('Error updating template:', error);
            window.location.reload();
        }
    });
});
</script>
