<div>
    @push('styles')
    <style>
        .form-section {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-section-title i {
            color: #F59E0B;
        }
    </style>
    @endpush

    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.mascotas.index') }}">Mascotas</a></li>
                <li class="breadcrumb-item active">Nueva Mascota</li>
            </ol>
        </nav>
        <h2 class="fw-semibold"><i class="ri ri-add-circle-line me-2"></i>Registrar Nueva Mascota</h2>
        <p class="text-muted">Complete la información del paciente veterinario</p>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit.prevent="save" enctype="multipart/form-data">
        <!-- Información Básica -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="ri ri-information-line"></i>
                Información Básica
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre de la Mascota <span class="text-danger">*</span></label>
                    <input type="text"
                           wire:model="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           placeholder="Ej: Max, Luna, etc."
                           required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Especie <span class="text-danger">*</span></label>
                    <select wire:model.live="especie_id"
                            class="form-select @error('especie_id') is-invalid @enderror"
                            required>
                        <option value="">Seleccione...</option>
                        @foreach($especies as $especie)
                            <option value="{{ $especie->id }}">{{ $especie->icono }} {{ $especie->nombre }}</option>
                        @endforeach
                    </select>
                    @error('especie_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Raza</label>
                    <select wire:model="raza_id"
                            class="form-select @error('raza_id') is-invalid @enderror"
                            {{ !$especie_id ? 'disabled' : '' }}>
                        <option value="">Seleccione...</option>
                        @foreach($razas as $raza)
                            <option value="{{ $raza->id }}">{{ $raza->nombre }}</option>
                        @endforeach
                    </select>
                    @error('raza_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sexo <span class="text-danger">*</span></label>
                    <select wire:model="sexo"
                            class="form-select @error('sexo') is-invalid @enderror"
                            required>
                        <option value="macho">♂ Macho</option>
                        <option value="hembra">♀ Hembra</option>
                    </select>
                    @error('sexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date"
                           wire:model="fecha_nacimiento"
                           class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                           max="{{ date('Y-m-d') }}">
                    @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Dejar vacío si se desconoce</small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Peso Actual (kg)</label>
                    <input type="number"
                           wire:model="peso_actual_kg"
                           class="form-control @error('peso_actual_kg') is-invalid @enderror"
                           step="0.01"
                           min="0.01"
                           max="999.99"
                           placeholder="0.00">
                    @error('peso_actual_kg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Color/Pelaje</label>
                    <input type="text"
                           wire:model="color_pelaje"
                           class="form-control @error('color_pelaje') is-invalid @enderror"
                           placeholder="Ej: Negro, Atigrado, etc.">
                    @error('color_pelaje') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Identificación -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="ri ri-id-card-line"></i>
                Identificación
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Número de Microchip</label>
                    <input type="text"
                           wire:model="microchip"
                           class="form-control @error('microchip') is-invalid @enderror"
                           placeholder="Número único de identificación">
                    @error('microchip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Número de Registro</label>
                    <input type="text"
                           wire:model="numero_registro"
                           class="form-control @error('numero_registro') is-invalid @enderror"
                           placeholder="Registro oficial">
                    @error('numero_registro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Foto de la Mascota</label>
                    <input type="file"
                           wire:model="foto"
                           class="form-control @error('foto') is-invalid @enderror"
                           accept="image/*">
                    @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Formatos: JPG, PNG. Máx: 2MB</small>

                    @if ($foto)
                        <div class="mt-2">
                            <img src="{{ $foto->temporaryUrl() }}"
                                 alt="Preview"
                                 style="max-width: 150px; border-radius: 8px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Salud -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="ri ri-heart-pulse-line"></i>
                Salud y Esterilización
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input"
                               type="checkbox"
                               wire:model="esterilizado"
                               id="esterilizado">
                        <label class="form-check-label" for="esterilizado">
                            Esterilizado/Castrado
                        </label>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha de Esterilización</label>
                    <input type="date"
                           wire:model="fecha_esterilizacion"
                           class="form-control @error('fecha_esterilizacion') is-invalid @enderror"
                           {{ !$esterilizado ? 'disabled' : '' }}
                           max="{{ date('Y-m-d') }}">
                    @error('fecha_esterilizacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Nivel de Agresividad</label>
                    <select wire:model="nivel_agresividad"
                            class="form-select @error('nivel_agresividad') is-invalid @enderror">
                        <option value="tranquilo">😌 Tranquilo</option>
                        <option value="nervioso">😰 Nervioso</option>
                        <option value="agresivo_leve">⚠️ Agresividad Leve</option>
                        <option value="agresivo">🚫 Agresivo</option>
                    </select>
                    @error('nivel_agresividad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-12">
                    <label class="form-label">Alergias Conocidas</label>
                    <textarea wire:model="alergias_conocidas"
                              class="form-control @error('alergias_conocidas') is-invalid @enderror"
                              rows="2"
                              placeholder="Liste las alergias conocidas..."></textarea>
                    @error('alergias_conocidas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Condiciones Crónicas</label>
                    <textarea wire:model="condiciones_cronicas"
                              class="form-control @error('condiciones_cronicas') is-invalid @enderror"
                              rows="2"
                              placeholder="Enfermedades o condiciones permanentes..."></textarea>
                    @error('condiciones_cronicas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Propietario -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="ri ri-user-line"></i>
                Propietario
            </div>

            @if(!$showNuevoPropietario)
                <div class="mb-3">
                    <label class="form-label">Seleccionar Propietario Existente</label>
                    <select wire:model="propietario_id"
                            class="form-select @error('propietario_id') is-invalid @enderror">
                        <option value="">-- Seleccione un propietario --</option>
                        @foreach($propietarios as $propietario)
                            <option value="{{ $propietario->id }}">
                                {{ $propietario->nombre_completo }} - {{ $propietario->telefono }}
                            </option>
                        @endforeach
                    </select>
                    @error('propietario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="button"
                        class="btn btn-outline-primary"
                        wire:click="toggleNuevoPropietario">
                    <i class="ri ri-add-line me-1"></i>Registrar Nuevo Propietario
                </button>
            @else
                <div class="alert alert-info">
                    <i class="ri ri-information-line me-2"></i>
                    Complete los datos del nuevo propietario
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text"
                               wire:model="prop_nombres"
                               class="form-control @error('prop_nombres') is-invalid @enderror"
                               required>
                        @error('prop_nombres') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text"
                               wire:model="prop_apellidos"
                               class="form-control @error('prop_apellidos') is-invalid @enderror"
                               required>
                        @error('prop_apellidos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Documento de Identidad</label>
                        <input type="text"
                               wire:model="prop_documento_identidad"
                               class="form-control @error('prop_documento_identidad') is-invalid @enderror">
                        @error('prop_documento_identidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="tel"
                               wire:model="prop_telefono"
                               class="form-control @error('prop_telefono') is-invalid @enderror">
                        @error('prop_telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email"
                               wire:model="prop_email"
                               class="form-control @error('prop_email') is-invalid @enderror">
                        @error('prop_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Dirección</label>
                        <input type="text"
                               wire:model="prop_direccion"
                               class="form-control @error('prop_direccion') is-invalid @enderror">
                        @error('prop_direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <button type="button"
                        class="btn btn-outline-secondary mt-3"
                        wire:click="toggleNuevoPropietario">
                    <i class="ri ri-arrow-go-back-line me-1"></i>Volver a Seleccionar
                </button>
            @endif
        </div>

        <!-- Notas Adicionales -->
        <div class="form-section">
            <div class="form-section-title">
                <i class="ri ri-sticky-note-line"></i>
                Notas Adicionales
            </div>

            <div class="mb-3">
                <label class="form-label">Marcas Distintivas</label>
                <textarea wire:model="marcas_distintivas"
                          class="form-control @error('marcas_distintivas') is-invalid @enderror"
                          rows="2"
                          placeholder="Cicatrices, manchas u otras características únicas..."></textarea>
                @error('marcas_distintivas') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-0">
                <label class="form-label">Notas Generales</label>
                <textarea wire:model="notas_generales"
                          class="form-control @error('notas_generales') is-invalid @enderror"
                          rows="3"
                          placeholder="Información adicional relevante..."></textarea>
                @error('notas_generales') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="d-flex justify-content-between gap-3">
            <a href="{{ route('admin.mascotas.index') }}" class="btn btn-secondary">
                <i class="ri ri-arrow-go-back-line me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ri ri-save-line me-1"></i>Guardar Mascota
            </button>
        </div>
    </form>
</div>
