<div>
<!-- Botón Flotante Principal -->
@if(!$isOpen)
<button type="button"
        class="floating-chat-button"
        wire:click="toggleWidget">
    <i class="ri ri-message-3-line"></i>
    @if($totalUnread > 0)
        <span class="floating-chat-button-badge">{{ $totalUnread > 99 ? '99+' : $totalUnread }}</span>
    @endif
</button>
@endif

<!-- Widget de Chat Flotante -->
@if($isOpen)
<div class="floating-chat-widget {{ $isMinimized ? 'minimized' : '' }}" id="floatingChatWidget">
    <!-- Header del Widget -->
    <div class="floating-chat-widget-header">
        <div class="d-flex align-items-center flex-grow-1">
            <i class="ri ri-message-3-fill text-white me-2 fs-5"></i>
            <h6 class="mb-0 text-white fw-semibold">Mensajería</h6>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-icon" wire:click="toggleMinimize" title="Minimizar">
                <i class="{{ $isMinimized ? 'ri-arrow-up-s-line' : 'ri-subtract-line' }} text-white"></i>
            </button>
            <button type="button" class="btn btn-sm btn-icon" wire:click="closeWidget" title="Cerrar">
                <i class="ri ri-close-line text-white"></i>
            </button>
        </div>
    </div>

    <!-- Contenido del Widget -->
    <div class="floating-chat-widget-content" id="widgetContent">
        <!-- Lista de Usuarios -->
        <div class="floating-chat-users" id="chatUsersList" style="{{ $selectedUserId ? 'display: none;' : '' }}">
            <!-- Buscador -->
            <div class="p-3 border-bottom">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="ri ri-search-line"></i>
                    </span>
                    <input type="text"
                           class="form-control border-start-0"
                           placeholder="Buscar contacto..."
                           wire:model.live.debounce.300ms="searchUser">
                </div>
            </div>

            <!-- Lista de Contactos -->
            <div class="floating-chat-users-list">
                @forelse($chatUsers as $user)
                    <div class="floating-chat-user-item" wire:click="selectUser({{ $user['id'] }})">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-shrink-0 position-relative">
                                @if($user['avatar'])
                                    <img src="{{ Storage::url($user['avatar']) }}" alt="Avatar" class="rounded-circle" width="40" height="40">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 14px;">
                                        {{ $user['initials'] }}
                                    </div>
                                @endif
                                @if($user['unread_count'] > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        {{ $user['unread_count'] > 9 ? '9+' : $user['unread_count'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex-grow-1 ms-3 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 text-truncate" style="font-size: 0.9rem;">{{ $user['name'] }}</h6>
                                    @if($user['last_message_time'])
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            {{ $user['last_message_time']->diffForHumans(null, true, true) }}
                                        </small>
                                    @endif
                                </div>
                                <p class="mb-0 text-muted text-truncate" style="font-size: 0.8rem;">
                                    {{ $user['last_message'] ?: $user['role'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="ri ri-user-search-line" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">No hay contactos disponibles</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Ventana de Chat -->
        <div class="floating-chat-conversation" id="chatConversation" style="{{ $selectedUserId ? '' : 'display: none;' }}">
            <!-- Header de Conversación -->
            <div class="floating-chat-conversation-header">
                <button type="button" class="btn btn-sm btn-icon me-2" wire:click="backToList">
                    <i class="ri ri-arrow-left-line"></i>
                </button>
                @if($selectedUser)
                <div class="flex-shrink-0">
                    @if($selectedUser->avatar)
                        <img src="{{ Storage::url($selectedUser->avatar) }}" alt="Avatar" class="rounded-circle" width="32" height="32">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                            {{ strtoupper(substr($selectedUser->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-grow-1 ms-2">
                    <h6 class="mb-0" style="font-size: 0.9rem;">{{ $selectedUser->name }}</h6>
                    <small class="text-muted" style="font-size: 0.75rem;">{{ $selectedUser->roles->first()?->name ?? 'Usuario' }}</small>
                </div>
                @endif
            </div>

            <!-- Mensajes -->
            <div class="floating-chat-messages" id="floatingChatMessages">
                @if(empty($messages))
                    <div class="text-center py-5 text-muted">
                        <i class="ri ri-chat-smile-2-line" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">No hay mensajes aún</p>
                        <small>Envía el primer mensaje</small>
                    </div>
                @else
                    @php $lastDate = null; @endphp
                    @foreach($messages as $msg)
                        @if($lastDate !== $msg['date'])
                            <div class="floating-chat-date-separator">
                                <span>{{ $msg['date'] }}</span>
                            </div>
                            @php $lastDate = $msg['date']; @endphp
                        @endif

                        <div class="floating-chat-message {{ $msg['is_mine'] ? 'mine' : 'theirs' }}">
                            <div class="floating-chat-bubble">
                                {{ $msg['message'] }}
                                <div class="floating-chat-time">
                                    {{ $msg['time'] }}
                                    @if($msg['is_mine'])
                                        @if($msg['is_read'])
                                            <i class="ri ri-check-double-fill text-info ms-1"></i>
                                        @else
                                            <i class="ri ri-check-double-fill ms-1"></i>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Input de Mensaje -->
            <div class="floating-chat-input-area">
                <form wire:submit.prevent="sendMessage" class="d-flex align-items-center gap-2 w-100">
                    <input type="text"
                           class="floating-chat-input"
                           wire:model="messageText"
                           placeholder="Escribe un mensaje..."
                           autocomplete="off"
                           id="floatingChatInput">
                    <button class="floating-chat-send-btn" type="submit" wire:loading.attr="disabled" wire:target="sendMessage">
                        <span wire:loading.remove wire:target="sendMessage"><i class="ri ri-send-plane-fill"></i></span>
                        <span wire:loading wire:target="sendMessage"><i class="ri ri-loader-4-line ri-spin"></i></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Polling para actualizar -->
<div wire:poll.3s="checkNewMessages" class="d-none"></div>

<style>
/* Botón Flotante Principal */
.floating-chat-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--bs-primary) 0%, #5f61e6 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 16px rgba(105, 108, 255, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    z-index: 1999;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.floating-chat-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(105, 108, 255, 0.5);
}

.floating-chat-button:active {
    transform: scale(0.95);
}

.floating-chat-button-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ff4d49;
    color: white;
    border-radius: 50%;
    min-width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
    border: 2px solid white;
    animation: pulse 2s infinite;
}

/* Widget de Chat */
.floating-chat-widget {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 380px;
    max-width: calc(100vw - 40px);
    height: 600px;
    max-height: calc(100vh - 120px);
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    z-index: 2000;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.floating-chat-widget.minimized {
    height: 56px;
}

.floating-chat-widget.minimized .floating-chat-widget-content {
    display: none;
}

.floating-chat-widget-header {
    background: linear-gradient(135deg, var(--bs-primary) 0%, #5f61e6 100%);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: move;
    user-select: none;
}

.floating-chat-widget-header .btn-icon {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.floating-chat-widget-header .btn-icon:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.floating-chat-widget-content {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Lista de Usuarios */
.floating-chat-users {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.floating-chat-users-list {
    flex: 1;
    overflow-y: auto;
}

.floating-chat-user-item {
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s;
    border-bottom: 1px solid #f0f0f0;
}

.floating-chat-user-item:hover {
    background-color: #f8f9fa;
}

.floating-chat-user-item:active {
    background-color: #e9ecef;
}

/* Conversación */
.floating-chat-conversation {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.floating-chat-conversation-header {
    padding: 12px 16px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    background: #f8f9fa;
}

.floating-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f8f9fa;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e0e0e0' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.floating-chat-date-separator {
    display: flex;
    justify-content: center;
    margin: 16px 0;
}

.floating-chat-date-separator span {
    background: #e1f2fb;
    color: #54656f;
    font-size: 0.7rem;
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 500;
}

.floating-chat-message {
    display: flex;
    margin-bottom: 8px;
}

.floating-chat-message.mine {
    justify-content: flex-end;
}

.floating-chat-message.theirs {
    justify-content: flex-start;
}

.floating-chat-bubble {
    max-width: 75%;
    padding: 8px 12px 20px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    line-height: 1.4;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.floating-chat-message.mine .floating-chat-bubble {
    background: var(--bs-primary);
    color: white;
    border-bottom-right-radius: 4px;
}

.floating-chat-message.theirs .floating-chat-bubble {
    background: white;
    color: var(--bs-body-color);
    border-bottom-left-radius: 4px;
}

.floating-chat-time {
    position: absolute;
    bottom: 3px;
    right: 8px;
    font-size: 0.65rem;
    opacity: 0.7;
    display: flex;
    align-items: center;
}

.floating-chat-input-area {
    padding: 12px 16px;
    background: white;
    border-top: 1px solid #e9ecef;
}

.floating-chat-input {
    flex: 1;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 0.85rem;
    outline: none;
    transition: border-color 0.2s;
}

.floating-chat-input:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.1);
}

.floating-chat-send-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: var(--bs-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s, background-color 0.2s;
    flex-shrink: 0;
}

.floating-chat-send-btn:hover {
    background: #5f61e6;
    transform: scale(1.05);
}

.floating-chat-send-btn:active {
    transform: scale(0.95);
}

@media (max-width: 576px) {
    .floating-chat-button {
        bottom: 15px;
        right: 15px;
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
    }

    .floating-chat-widget {
        width: calc(100vw - 20px);
        right: 10px;
        bottom: 75px;
        height: calc(100vh - 100px);
    }
}
</style>

@push('scripts')
<script>
if (typeof floatingChatInitialized === 'undefined') {
    window.floatingChatInitialized = true;

    document.addEventListener('livewire:init', function() {
        let isDragging = false;
        let currentX, currentY, initialX, initialY;
        let xOffset = 0, yOffset = 0;
        let chatWidget = null;

        // Inicializar drag cuando el widget esté visible
        Livewire.hook('morph.updated', ({ el }) => {
            if (el.id === 'floatingChatWidget') {
                chatWidget = el;
                initDrag();
            }
        });

        function initDrag() {
            const header = document.querySelector('.floating-chat-widget-header');
            if (!header) return;

            header.removeEventListener('mousedown', dragStart);
            header.removeEventListener('touchstart', dragStart);
            header.addEventListener('mousedown', dragStart);
            header.addEventListener('touchstart', dragStart);
        }

        function dragStart(e) {
            if (e.target.closest('button')) return;

            chatWidget = document.getElementById('floatingChatWidget');
            if (!chatWidget) return;

            isDragging = true;
            if (e.type === 'touchstart') {
                initialX = e.touches[0].clientX - xOffset;
                initialY = e.touches[0].clientY - yOffset;
            } else {
                initialX = e.clientX - xOffset;
                initialY = e.clientY - yOffset;
            }
            chatWidget.style.transition = 'none';
        }

        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag);

        function drag(e) {
            if (isDragging && chatWidget) {
                e.preventDefault();
                if (e.type === 'touchmove') {
                    currentX = e.touches[0].clientX - initialX;
                    currentY = e.touches[0].clientY - initialY;
                } else {
                    currentX = e.clientX - initialX;
                    currentY = e.clientY - initialY;
                }
                xOffset = currentX;
                yOffset = currentY;
                chatWidget.style.transform = `translate(${currentX}px, ${currentY}px)`;
            }
        }

        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('touchend', dragEnd);

        function dragEnd() {
            if (isDragging && chatWidget) {
                isDragging = false;
                chatWidget.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            }
        }

        // Auto-scroll en mensajes
        Livewire.hook('morph.updated', ({ el }) => {
            if (el.id === 'floatingChatMessages') {
                setTimeout(() => {
                    el.scrollTop = el.scrollHeight;
                }, 100);
            }
        });

        // Scroll al enviar mensaje
        window.addEventListener('message-sent-widget', () => {
            const messagesDiv = document.getElementById('floatingChatMessages');
            if (messagesDiv) {
                setTimeout(() => {
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }, 100);
            }
        });
    });
}
</script>
@endpush
</div>
