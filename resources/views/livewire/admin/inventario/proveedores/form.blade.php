<div>
    @section('title', $isEdit ? 'Editar Proveedor' : 'Nuevo Proveedor')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="ri ri-truck-line me-2"></i>{{ $isEdit ? 'Editar' : 'Nuevo' }} Proveedor</h2>
        <a href="{{ route('admin.inventario.proveedores.index') }}" class="btn btn-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>Volver
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row">
            <!-- Columna principal -->
            <div class="col-md-8">
                <!-- Información básica -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Información del Proveedor</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label>Nombre / Razón Social *</label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                           wire:model="nombre" placeholder="Nombre del proveedor">
                                    @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Documento (RIF/NIT)</label>
                                    <input type="text" class="form-control" wire:model="documento">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Persona de Contacto</label>
                                    <input type="text" class="form-control" wire:model="contacto">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Teléfono</label>
                                    <input type="text" class="form-control" wire:model="telefono">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           wire:model="email">
                                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Condiciones de Pago</label>
                                    <input type="text" class="form-control" wire:model="condiciones_pago"
                                           placeholder="Ej: 30 días, contado">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0"><i class="ri ri-map-pin-line me-2"></i>Ubicación</h5></div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Dirección</label>
                            <textarea class="form-control @error('direccion') is-invalid @enderror"
                                      wire:model="direccion" rows="2"
                                      placeholder="Se completará automáticamente al seleccionar en el mapa"></textarea>
                            @error('direccion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Latitud</label>
                                    <input type="text" class="form-control @error('latitud') is-invalid @enderror"
                                           wire:model="latitud" placeholder="Seleccione en el mapa" readonly>
                                    @error('latitud') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Longitud</label>
                                    <input type="text" class="form-control @error('longitud') is-invalid @enderror"
                                           wire:model="longitud" placeholder="Seleccione en el mapa" readonly>
                                    @error('longitud') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Mapa -->
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <span class="fw-semibold"><i class="ri ri-map-2-line me-1"></i>Seleccionar en el mapa</span>
                                <button type="button" onclick="getCurrentLocation()" class="btn btn-primary btn-sm">
                                    <i class="ri ri-crosshair-line me-1"></i>Mi ubicación
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div wire:ignore id="map-proveedor" style="height:400px;"></div>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="ri ri-information-line me-1"></i>
                            Haz clic en el mapa o arrastra el marcador para seleccionar la ubicación del proveedor.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Columna lateral -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Opciones</h5></div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="status" id="status">
                            <label class="form-check-label" for="status">Activo</label>
                        </div>
                    </div>
                </div>

                <!-- Mini preview coordenadas -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Coordenadas</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Latitud:</small>
                            <small class="fw-bold font-monospace">{{ $latitud }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Longitud:</small>
                            <small class="fw-bold font-monospace">{{ $longitud }}</small>
                        </div>
                        @if($latitud && $longitud)
                            <a href="https://www.google.com/maps?q={{ $latitud }},{{ $longitud }}"
                               target="_blank" class="btn btn-sm btn-outline-secondary w-100 mt-2">
                                <i class="ri ri-external-link-line me-1"></i>Ver en Google Maps
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri ri-save-line me-1"></i>{{ $isEdit ? 'Actualizar' : 'Guardar' }} Proveedor
                            </button>
                            <a href="{{ route('admin.inventario.proveedores.index') }}" class="btn btn-secondary">
                                <i class="ri ri-close-line me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('livewire:init', function () {
        var initialLat = {{ $latitud ?? 10.4806 }};
        var initialLng = {{ $longitud ?? -66.9036 }};

        var map = L.map('map-proveedor').setView([initialLat, initialLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

        function updateLocation(lat, lng) {
            @this.set('latitud', lat);
            @this.set('longitud', lng);

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=es`)
                .then(response => response.json())
                .then(data => {
                    if (data.display_name) {
                        @this.set('direccion', data.display_name);
                    }
                })
                .catch(() => {});
        }

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateLocation(e.latlng.lat, e.latlng.lng);
        });

        marker.on('dragend', function (e) {
            var latlng = marker.getLatLng();
            updateLocation(latlng.lat, latlng.lng);
        });

        window.getCurrentLocation = function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 15);
                    updateLocation(lat, lng);
                }, function () {
                    alert('No se pudo obtener tu ubicación.');
                });
            } else {
                alert('Tu navegador no soporta geolocalización.');
            }
        };
    });
</script>
@endpush
