<div>
    <!-- Header con información de conexión -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">
                        <i class="ri ri-send-plane-line me-2 text-primary"></i>Enviar Mensajes WhatsApp
                    </h4>
                    <p class="text-muted mb-0">Envía mensajes individuales o masivos a pacientes y médicos</p>
                </div>
                <div class="d-flex gap-2">
                    <button wire:click="loadRecentMessages" class="btn btn-outline-primary btn-sm">
                        <i class="ri ri-refresh-line me-1"></i>Actualizar
                    </button>
                    <button wire:click="clearForm" class="btn btn-outline-secondary btn-sm">
                        <i class="ri ri-delete-bin-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de estado -->
    @if($success)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri ri-checkbox-circle-line me-2"></i>{{ $success }}
            <button type="button" class="btn-close" wire:click="$set('success', null)"></button>
        </div>
    @endif

    @if($error)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri ri-error-warning-line me-2"></i>{{ $error }}
            <button type="button" class="btn-close" wire:click="$set('error', null)"></button>
        </div>
    @endif

    <!-- Modo de envío -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="ri ri-send-plane-2-line me-2"></i>Modo de Envío
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model.live="sendMode" 
                               id="individual" value="individual">
                        <label class="form-check-label" for="individual">
                            <i class="ri ri-user-line me-1"></i>Individual
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model.live="sendMode" 
                               id="grupal" value="grupal">
                        <label class="form-check-label" for="grupal">
                            <i class="ri ri-contacts-line me-1"></i>Grupal
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de envío individual -->
    @if($sendMode === 'individual')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="ri ri-user-line me-2"></i>Envío Individual
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="to" class="form-label">Número de teléfono *</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ri ri-phone-line"></i>
                            </span>
                            <input type="tel" 
                                   class="form-control @error('to') is-invalid @enderror"
                                   id="to"
                                   wire:model.live="to"
                                   placeholder="5917XXXXXXX"
                                   maxlength="15">
                        </div>
                        @error('to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Incluir código de país (ej: 591 para Bolivia)</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Plantillas rápidas</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($templates as $template)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary"
                                        wire:click="useTemplate('{{ $template['id'] }}')">
                                    {{ $template['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label for="message" class="form-label">Mensaje *</label>
                        <textarea class="form-control @error('message') is-invalid @enderror"
                                  id="message"
                                  wire:model.live="message"
                                  rows="4"
                                  placeholder="Escribe tu mensaje aquí..."
                                  maxlength="1000"></textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">
                                <i class="ri ri-character-recognition-line me-1"></i>
                                Caracteres: {{ $charCount }}/1000
                            </small>
                            @if($selectedTemplate)
                                <small class="text-primary">
                                    <i class="ri ri-price-tag-3-line me-1"></i>
                                    Plantilla: {{ collect($templates)->firstWhere('id', $selectedTemplate)['name'] }}
                                </small>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button wire:click="sendMessage" 
                                    class="btn btn-primary"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="disabled">
                                <span wire:loading.remove wire:target="sendMessage">
                                    <i class="ri ri-send-plane-line me-1"></i>Enviar Mensaje
                                </span>
                                <span wire:loading wire:target="sendMessage">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Enviando...
                                </span>
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" wire:click="clearForm">
                                <i class="ri ri-delete-bin-line me-1"></i>Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulario de envío grupal -->
    @if($sendMode === 'grupal')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="ri ri-contacts-line me-2"></i>Envío Grupal
                </h5>
            </div>
            <div class="card-body">
                <!-- Filtros de contactos -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de contacto</label>
                        <select class="form-select" wire:model.live="targetGroup">
                            <option value="pacientes">Pacientes</option>
                            <option value="medicos">Médicos</option>
                        </select>
                    </div>
                    
                    @if($targetGroup === 'pacientes')
                        <div class="col-md-4">
                            <label class="form-label">Médico tratante</label>
                            <select class="form-select" wire:model.live="filterMedico">
                                <option value="">Todos los médicos</option>
                                @foreach($medicos as $medico)
                                    <option value="{{ $medico->id }}">Dr. {{ $medico->nombres }} {{ $medico->apellidos }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-md-4">
                            <label class="form-label">Especialidad</label>
                            <select class="form-select" wire:model.live="filterEspecialidad">
                                <option value="">Todas las especialidades</option>
                                @foreach($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-outline-primary w-100" wire:click="loadContacts">
                                <i class="ri ri-refresh-line me-1"></i>Actualizar lista
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selección de contactos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="ri ri-group-line me-2"></i>Seleccionar Destinatarios
                                        <span class="badge bg-primary ms-2">{{ $selectedCount }} seleccionados</span>
                                        <span class="badge bg-success ms-1">{{ $selectedWithPhoneCount }} con teléfono</span>
                                    </h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model.live="selectAll" id="selectAll">
                                        <label class="form-check-label" for="selectAll">
                                            Seleccionar todos
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                                @if(count($contacts) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th width="50"></th>
                                                    <th>Nombre</th>
                                                    @if($targetGroup === 'pacientes')
                                                        <th>Edad</th>
                                                    @else
                                                        <th>Especialidad</th>
                                                    @endif
                                                    <th>Teléfono</th>
                                                    <th>Email</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($contacts as $contact)
                                                    <tr class="@if(in_array($contact['id'], $selectedContacts)) table-primary @endif">
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       wire:model.live="selectedContacts" 
                                                                       value="{{ $contact['id'] }}"
                                                                       id="contact_{{ $contact['id'] }}"
                                                                       @if(!$contact['tiene_telefono']) disabled @endif>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <label for="contact_{{ $contact['id'] }}" class="mb-0">
                                                                {{ $contact['nombre'] }}
                                                                @if($contact['tipo'] === 'medico')
                                                                    <span class="badge bg-info ms-2">Médico</span>
                                                                @else
                                                                    <span class="badge bg-success ms-2">Paciente</span>
                                                                @endif
                                                            </label>
                                                        </td>
                                                        @if($targetGroup === 'pacientes')
                                                            <td>{{ $contact['edad'] }} años</td>
                                                        @else
                                                            <td>{{ $contact['especialidad'] ?? 'General' }}</td>
                                                        @endif
                                                        <td>
                                                            @if($contact['tiene_telefono'])
                                                                <span class="badge bg-success">
                                                                    <i class="ri ri-check-line me-1"></i>{{ $contact['telefono'] }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-danger">
                                                                    <i class="ri ri-close-line me-1"></i>Sin teléfono
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $contact['email'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="ri ri-user-search-line ri-2x text-muted mb-3"></i>
                                        <p class="mb-0">No se encontraron {{ $targetGroup === 'pacientes' ? 'pacientes' : 'médicos' }} con los filtros aplicados</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje y envío -->
                <div class="row g-3">
                    <div class="col-12">
                        <label for="bulkMessage" class="form-label">Mensaje *</label>
                        <textarea class="form-control @error('message') is-invalid @enderror"
                                  id="bulkMessage"
                                  wire:model.live="message"
                                  rows="3"
                                  placeholder="Escribe tu mensaje aquí... Puedes usar {nombre}, {paciente}, {medico}"
                                  maxlength="1000"></textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="ri ri-information-line me-1"></i>
                            Variables disponibles: {nombre}, {paciente}, {medico}, {especialidad}
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex gap-2 align-items-center">
                            <button wire:click="sendBulkMessages" 
                                    class="btn btn-primary"
                                    wire:loading.attr="disabled"
                                    @if($selectedWithPhoneCount === 0) disabled @endif>
                                <span wire:loading.remove wire:target="sendBulkMessages">
                                    <i class="ri ri-send-plane-line me-1"></i>
                                    Enviar a {{ $selectedWithPhoneCount }} contactos
                                </span>
                                <span wire:loading wire:target="sendBulkMessages">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Enviando... {{ $sendProgress }}/{{ $sendTotal }}
                                </span>
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" wire:click="clearForm">
                                <i class="ri ri-delete-bin-line me-1"></i>Cancelar
                            </button>
                            
                            @if($isSendingBulk)
                                <div class="progress flex-grow-1" style="max-width: 300px;">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: {{ $sendTotal > 0 ? ($sendProgress / $sendTotal) * 100 : 0 }}%">
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        @if($isSendingBulk)
                            <div class="mt-2">
                                <small class="text-muted">
                                    Progreso: {{ $sendProgress }}/{{ $sendTotal }} 
                                    @if(isset($sendResults['success']))
                                        • Exitosos: {{ $sendResults['success'] }}
                                    @endif
                                    @if(isset($sendResults['failed']))
                                        • Fallidos: {{ $sendResults['failed'] }}
                                    @endif
                                    @if(isset($sendResults['skipped']))
                                        • Sin teléfono: {{ $sendResults['skipped'] }}
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Mensajes recientes -->
    @if(count($recentMessages) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="ri ri-history-line me-2"></i>Mensajes Recientes
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Destinatario</th>
                                <th>Mensaje</th>
                                <th>Estado</th>
                                <th width="100">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMessages as $msg)
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($msg['created_at'] ?? now())->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td>{{ $msg['to'] ?? 'N/A' }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;">
                                            {{ Str::limit($msg['message'] ?? '', 60) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="ri ri-check-line me-1"></i>Enviado
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                wire:click="resend('{{ $msg['to'] ?? '' }}', '{{ addslashes($msg['message'] ?? '') }}')"
                                                title="Reenviar">
                                            <i class="ri ri-refresh-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading overlay -->
    <div wire:loading.flex wire:target="sendMessage, sendBulkMessages" 
         class="position-fixed top-0 start-0 w-100 h-100 justify-content-center align-items-center" 
         style="background: rgba(255,255,255,0.8); z-index: 1050;">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Enviando...</span>
            </div>
            <h5 class="mb-2">Procesando envío</h5>
            <p class="text-muted mb-0">Por favor espere un momento</p>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.form-check-input:checked {
    background-color: #666cff;
    border-color: #666cff;
}

.btn-primary {
    background-color: #666cff;
    border-color: #666cff;
}

.progress-bar {
    background-color: #666cff;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
}

.badge {
    font-weight: 500;
}

.text-truncate {
    display: inline-block;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    // Validación de número de teléfono en tiempo real
    Livewire.on('validatePhone', (number) => {
        const cleaned = number.replace(/\D/g, '');
        if (cleaned.length < 10) {
            console.warn('Número de teléfono muy corto');
        }
    });
    
    // Auto-scroll al inicio después de enviar
    Livewire.on('messageSent', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Manejo de teclas de acceso rápido
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter para enviar
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            @this.sendMessage();
        }
        
        // ESC para limpiar formulario
        if (e.key === 'Escape') {
            @this.clearForm();
        }
    });
});
</script>
@endpush