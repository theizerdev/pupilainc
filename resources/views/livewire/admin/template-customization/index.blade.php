<div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Personalización de Plantilla</h5>
                    <button type="button" class="btn btn-outline-secondary" wire:click="resetToDefaults">
                        <i class="ri-refresh-line me-1"></i>Restaurar Valores por Defecto
                    </button>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Colores y Tema -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Colores y Tema</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Color Primario</label>
                                        <input type="color" class="form-control form-control-color" wire:model.live="primary_color">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tema</label>
                                        <select class="form-select" wire:model.live="theme">
                                            <option value="light">Claro</option>
                                            <option value="dark">Oscuro</option>
                                            <option value="system">Sistema</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Estilo</label>
                                        <select class="form-select" wire:model.live="skin">
                                            <option value="0">Por Defecto</option>
                                            <option value="1">Con Bordes</option>
                                        </select>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model.live="semi_dark">
                                        <label class="form-check-label">Menú Semi-Oscuro</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Layout -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Diseño</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Layout</label>
                                        <select class="form-select" wire:model.live="layout_type">
                                            <option value="vertical">Vertical</option>
                                            <option value="horizontal">Horizontal</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ancho de Contenido</label>
                                        <select class="form-select" wire:model.live="content_layout">
                                            <option value="compact">Compacto</option>
                                            <option value="wide">Ancho</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dirección de Texto</label>
                                        <select class="form-select" wire:model.live="text_direction">
                                            <option value="ltr">Izquierda a Derecha</option>
                                            <option value="rtl">Derecha a Izquierda</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menú y Navegación -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Menú y Navegación</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Navbar</label>
                                        <select class="form-select" wire:model.live="navbar_type">
                                            <option value="sticky">Pegajoso</option>
                                            <option value="static">Estático</option>
                                            <option value="hidden">Oculto</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Header</label>
                                        <select class="form-select" wire:model.live="header_type">
                                            <option value="static">Estático</option>
                                            <option value="fixed">Fijo</option>
                                        </select>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model.live="menu_collapsed">
                                        <label class="form-check-label">Menú Colapsado</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model.live="footer_fixed">
                                        <label class="form-check-label">Footer Fijo</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model.live="dropdown_on_hover">
                                        <label class="form-check-label">Dropdown al Pasar Mouse</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vista Previa -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Vista Previa</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="preview-container" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: {{ $theme === 'dark' ? '#2b2c40' : '#fff' }};">
                                        <div style="background: {{ $primary_color }}; height: 40px; border-radius: 4px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                                            Header ({{ ucfirst($header_type) }})
                                        </div>
                                        <div class="d-flex" style="height: 100px;">
                                            @if($layout_type === 'vertical')
                                                <div style="width: {{ $menu_collapsed ? '60px' : '200px' }}; background: {{ $semi_dark ? '#3a3e5c' : '#f8f9fa' }}; border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                    Menú {{ $menu_collapsed ? 'Mini' : 'Completo' }}
                                                </div>
                                            @endif
                                            <div style="flex: 1; background: {{ $theme === 'dark' ? '#25293c' : '#f8f9fa' }}; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                Contenido ({{ ucfirst($content_layout) }})
                                            </div>
                                        </div>
                                        @if($layout_type === 'horizontal')
                                            <div style="background: {{ $semi_dark ? '#3a3e5c' : '#e9ecef' }}; height: 30px; border-radius: 4px; margin-top: 10px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                Menú Horizontal
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ri-save-line me-1"></i>Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </div>
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