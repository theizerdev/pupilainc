<div>
    <!-- Conversations Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">
                        <i class="ri ri-message-2-line me-2 text-primary"></i>Conversaciones
                    </h4>
                    <p class="text-muted mb-0">Gestiona tus conversaciones de WhatsApp</p>
                </div>
                <div class="d-flex gap-2">
                    <button wire:click="refreshConversations" 
                            class="btn btn-outline-primary btn-sm"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="refreshConversations">
                            <i class="ri ri-refresh-line me-1"></i>Actualizar
                        </span>
                        <span wire:loading wire:target="refreshConversations">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        </span>
                    </button>
                    <button wire:click="toggleView('grid')" 
                            class="btn btn-outline-secondary btn-sm {{ $viewMode === 'grid' ? 'active' : '' }}">
                        <i class="ri ri-grid-line"></i>
                    </button>
                    <button wire:click="toggleView('list')" 
                            class="btn btn-outline-secondary btn-sm {{ $viewMode === 'list' ? 'active' : '' }}">
                        <i class="ri ri-list-unordered"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="card-body pt-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri ri-search-line"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               placeholder="Buscar contactos..."
                               wire:model.live.debounce.300ms="searchTerm">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">Todos los estados</option>
                        <option value="unread">No leídos</option>
                        <option value="read">Leídos</option>
                        <option value="archived">Archivados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="sortBy">
                        <option value="last_message">Último mensaje</option>
                        <option value="name">Nombre</option>
                        <option value="unread_count">Mensajes no leídos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-danger w-100" wire:click="clearFilters">
                        <i class="ri ri-delete-bin-line me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversations Grid/List View -->
    @if(count($conversations) > 0)
        <div class="row g-4">
            @foreach($conversations as $conversation)
                <div class="col-12 {{ $viewMode === 'grid' ? 'col-lg-6 col-xl-4' : 'col-12' }}">
                    <div class="card border-0 shadow-sm h-100 conversation-card {{ $conversation['active'] ? 'border-start border-4 border-primary' : '' }}"
                         wire:click="selectConversation('{{ $conversation['id'] }}')"
                         style="cursor: pointer;">
                        <div class="card-body p-3">
                            <div class="d-flex {{ $viewMode === 'grid' ? 'flex-column text-center' : 'align-items-center' }}">
                                <!-- Avatar -->
                                <div class="flex-shrink-0 mb-2 mb-md-0 me-0 me-md-3">
                                    <div class="position-relative">
                                        <div class="avatar {{ $viewMode === 'grid' ? 'avatar-xl' : 'avatar-lg' }} me-3">
                                            @if($conversation['avatar'])
                                                <img src="{{ $conversation['avatar'] }}" 
                                                     alt="{{ $conversation['name'] }}" 
                                                     class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ strtoupper(substr($conversation['name'], 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($conversation['online'])
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle">
                                                <span class="visually-hidden">Online</span>
                                            </span>
                                        @endif
                                        @if($conversation['unread_count'] > 0)
                                            <span class="position-absolute top-0 end-0 badge rounded-pill bg-danger">
                                                {{ $conversation['unread_count'] > 99 ? '99+' : $conversation['unread_count'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Conversation Info -->
                                <div class="flex-grow-1 {{ $viewMode === 'grid' ? 'text-center mt-2' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 {{ $conversation['unread_count'] > 0 ? 'fw-bold text-primary' : '' }}">
                                            {{ $conversation['name'] }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($conversation['last_message_time'])->diffForHumans() }}
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-truncate {{ $viewMode === 'grid' ? 'mb-2' : '' }}" 
                                             style="max-width: {{ $viewMode === 'grid' ? '100%' : '200px' }};">
                                            @if($conversation['last_message_type'] === 'image')
                                                <i class="ri ri-image-line text-muted me-1"></i>
                                                <span class="text-muted">Imagen enviada</span>
                                            @elseif($conversation['last_message_type'] === 'document')
                                                <i class="ri ri-file-line text-muted me-1"></i>
                                                <span class="text-muted">Documento enviado</span>
                                            @else
                                                <span class="{{ $conversation['unread_count'] > 0 ? 'fw-medium' : 'text-muted' }}">
                                                    {{ Str::limit($conversation['last_message'], 60) }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($viewMode === 'list')
                                            <div class="d-flex gap-1">
                                                @if(($conversation['total_messages'] ?? 0) > 0)
                                                    <span class="badge bg-label-info" title="{{ $conversation['total_messages'] }} mensajes">
                                                        <i class="ri ri-message-2-line me-1"></i>{{ $conversation['total_messages'] }}
                                                    </span>
                                                @endif
                                                @if($conversation['pinned'])
                                                    <span class="badge bg-label-warning" title="Fijado">
                                                        <i class="ri ri-pushpin-line"></i>
                                                    </span>
                                                @endif
                                                @if($conversation['muted'])
                                                    <span class="badge bg-label-secondary" title="Silenciado">
                                                        <i class="ri ri-volume-mute-line"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($viewMode === 'grid')
                                        <div class="d-flex justify-content-center gap-1 mt-2">
                                            @if(($conversation['total_messages'] ?? 0) > 0)
                                                <span class="badge bg-label-info">
                                                    <i class="ri ri-message-2-line me-1"></i>{{ $conversation['total_messages'] }} msgs
                                                </span>
                                            @endif
                                            @if($conversation['pinned'])
                                                <span class="badge bg-label-warning">
                                                    <i class="ri ri-pushpin-line me-1"></i>Fijado
                                                </span>
                                            @endif
                                            @if($conversation['muted'])
                                                <span class="badge bg-label-secondary">
                                                    <i class="ri ri-volume-mute-line me-1"></i>Silenciado
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
       
    @else
        <!-- Empty State -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="avatar avatar-xxl mb-4">
                    <span class="avatar-initial rounded-circle bg-label-secondary">
                        <i class="ri ri-message-2-line ri-36px"></i>
                    </span>
                </div>
                <h4 class="mb-2">No hay conversaciones</h4>
                <p class="text-muted mb-4">
                    @if($searchTerm || $filterStatus)
                        No se encontraron conversaciones que coincidan con los filtros.
                    @else
                        Aún no tienes conversaciones. Los contactos aparecerán aquí cuando envíes o reciban mensajes.
                    @endif
                </p>
                
                @if($searchTerm || $filterStatus)
                    <button class="btn btn-outline-primary" wire:click="clearFilters">
                        <i class="ri ri-filter-off-line me-1"></i>Limpiar filtros
                    </button>
                @else
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-primary" wire:click="$dispatch('openSendMessageModal')">
                            <i class="ri ri-send-plane-line me-1"></i>Enviar mensaje
                        </button>
                        <button class="btn btn-outline-secondary" wire:click="refreshConversations">
                            <i class="ri ri-refresh-line me-1"></i>Actualizar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading.flex wire:target="refreshConversations, selectConversation" 
         class="position-fixed top-0 start-0 w-100 h-100 justify-content-center align-items-center" 
         style="background: rgba(255,255,255,0.8); z-index: 1050;">
        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <p class="mb-0 text-muted">Cargando conversaciones...</p>
        </div>
    </div>
</div>

@push('styles')
<style>
.conversation-card {
    transition: all 0.2s ease;
    border: 1px solid rgba(0,0,0,0.125);
}

.conversation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    border-color: #666cff;
}

.conversation-card.active {
    border-left: 4px solid #666cff !important;
    background-color: rgba(102, 108, 255, 0.03);
}

.text-truncate {
    display: inline-block;
}

.badge {
    font-weight: 500;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #666cff;
}

.pagination .page-item.active .page-link {
    background-color: #666cff;
    border-color: #666cff;
}

.form-control:focus, .form-select:focus {
    border-color: #666cff;
    box-shadow: 0 0 0 0.2rem rgba(102, 108, 255, 0.25);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search focus
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.querySelector('input[wire\\:model^="searchTerm"]').focus();
        }
        
        // ESC to clear search
        if (e.key === 'Escape') {
            @this.clearFilters();
        }
    });
    
    // Conversation selection handling
    Livewire.on('conversationSelected', (conversationId) => {
        // Scroll to top when selecting conversation
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Add visual feedback
        const selectedCard = document.querySelector(`[wire\\:click="selectConversation('${conversationId}')"]`);
        if (selectedCard) {
            selectedCard.classList.add('active');
            setTimeout(() => {
                selectedCard.classList.remove('active');
            }, 2000);
        }
    });
    
    // Auto-refresh conversations every 30 seconds
    const refreshInterval = setInterval(() => {
        @this.refreshConversations();
    }, 30000);
    
    // Cleanup on component destruction
    document.addEventListener('livewire:destruct', () => {
        clearInterval(refreshInterval);
    });
});
</script>
@endpush