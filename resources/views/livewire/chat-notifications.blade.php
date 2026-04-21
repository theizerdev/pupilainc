<div wire:poll.5s="checkNewMessages">

<!-- Polling para actualizar la ventana flotante -->
<div wire:poll.3s="refreshFloatingChat" class="d-none"></div>

<!-- Ventana Flotante de Chat -->
<div wire:ignore.self id="chatFloatingWindow" class="chat-floating-window" style="display: none;">
    <div class="chat-floating-header">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="chat-floating-avatar me-2">
                @if($replyingToUser && $replyingToUser->avatar)
                    <img src="{{ Storage::url($replyingToUser->avatar) }}" alt="Avatar" class="rounded-circle" width="32" height="32">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                        {{ $replyingToUser ? strtoupper(substr($replyingToUser->name, 0, 2)) : '?' }}
                    </div>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold text-white" style="font-size: 0.9rem;">{{ $replyingToUser->name ?? 'Chat' }}</div>
                <div class="text-white-50" style="font-size: 0.75rem;">Activo ahora</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-icon" id="minimizeChat" title="Minimizar">
                <i class="ri ri-subtract-line text-white" id="minimizeIcon"></i>
                <span class="chat-floating-badge" id="floatingBadge" style="display: none;">0</span>
            </button>
            <button type="button" class="btn btn-sm btn-icon" id="closeChat" title="Cerrar">
                <i class="ri ri-close-line text-white"></i>
            </button>
        </div>
    </div>

    <div class="chat-floating-body" id="chatFloatingBody">
        @if(!empty($recentMessages))
            @foreach($recentMessages as $msg)
                @php $isMine = $msg['sender_id'] === auth()->id(); @endphp
                <div class="chat-floating-message {{ $isMine ? 'mine' : 'theirs' }}">
                    <div class="chat-floating-bubble">
                        {{ $msg['message'] }}
                        <div class="chat-floating-time">
                            {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center text-muted py-4" style="font-size: 0.85rem;">No hay mensajes previos</div>
        @endif
    </div>

    <div class="chat-floating-footer">
        <form wire:submit.prevent="sendReply" class="d-flex align-items-center gap-2 w-100">
            <input type="text"
                   class="chat-floating-input"
                   wire:model="replyMessage"
                   placeholder="Escribe un mensaje..."
                   autocomplete="off"
                   id="chatFloatingInput">
            <button class="chat-floating-send" type="submit" wire:loading.attr="disabled" wire:target="sendReply">
                <span wire:loading.remove wire:target="sendReply"><i class="ri ri-send-plane-fill"></i></span>
                <span wire:loading wire:target="sendReply"><i class="ri ri-loader-4-line ri-spin"></i></span>
            </button>
        </form>
        @error('replyMessage') <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
    </div>
</div>

<style>
.chat-floating-window {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 360px;
    max-width: calc(100vw - 40px);
    height: 500px;
    max-height: calc(100vh - 100px);
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    z-index: 2000;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-floating-window.minimized {
    height: 56px;
}

.chat-floating-window.minimized .chat-floating-body,
.chat-floating-window.minimized .chat-floating-footer {
    display: none;
}

.chat-floating-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ff4d49;
    color: white;
    border-radius: 50%;
    min-width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(255, 77, 73, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.chat-floating-header {
    background: linear-gradient(135deg, var(--bs-primary) 0%, #5f61e6 100%);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: move;
    user-select: none;
}

.chat-floating-header .btn-icon {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s;
    position: relative;
}

.chat-floating-header .btn-icon:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.chat-floating-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f8f9fa;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e0e0e0' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.chat-floating-message {
    display: flex;
    margin-bottom: 12px;
}

.chat-floating-message.mine {
    justify-content: flex-end;
}

.chat-floating-message.theirs {
    justify-content: flex-start;
}

.chat-floating-bubble {
    max-width: 75%;
    padding: 8px 12px 20px 12px;
    border-radius: 12px;
    font-size: 0.9rem;
    line-height: 1.4;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.chat-floating-message.mine .chat-floating-bubble {
    background: var(--bs-primary);
    color: white;
    border-bottom-right-radius: 4px;
}

.chat-floating-message.theirs .chat-floating-bubble {
    background: white;
    color: var(--bs-body-color);
    border-bottom-left-radius: 4px;
}

.chat-floating-time {
    position: absolute;
    bottom: 3px;
    right: 8px;
    font-size: 0.7rem;
    opacity: 0.7;
}

.chat-floating-footer {
    padding: 12px 16px;
    background: white;
    border-top: 1px solid #e9ecef;
}

.chat-floating-input {
    flex: 1;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.2s;
}

.chat-floating-input:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.1);
}

.chat-floating-send {
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

.chat-floating-send:hover {
    background: #5f61e6;
    transform: scale(1.05);
}

.chat-floating-send:active {
    transform: scale(0.95);
}

@media (max-width: 576px) {
    .chat-floating-window {
        width: calc(100vw - 20px);
        right: 10px;
        bottom: 10px;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Crear audio usando Web Audio API para evitar problemas de archivos corruptos
    let audioContext = null;
    let notificationBuffer = null;

    // Inicializar AudioContext en la primera interacción del usuario
    const initAudio = () => {
        if (!audioContext) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            // Crear un tono simple de notificación
            createNotificationSound();
        }
        document.body.removeEventListener('click', initAudio);
        document.body.removeEventListener('keydown', initAudio);
        document.body.removeEventListener('touchstart', initAudio);
    };

    // Crear sonido de notificación usando Web Audio API
    function createNotificationSound() {
        if (!audioContext) return;

        const sampleRate = audioContext.sampleRate;
        const duration = 0.3; // 300ms
        const buffer = audioContext.createBuffer(1, sampleRate * duration, sampleRate);
        const data = buffer.getChannelData(0);

        // Generar un tono agradable (dos tonos: 800Hz y 1000Hz)
        for (let i = 0; i < buffer.length; i++) {
            const t = i / sampleRate;
            const envelope = Math.exp(-3 * t); // Decay exponencial
            data[i] = envelope * (Math.sin(2 * Math.PI * 800 * t) + Math.sin(2 * Math.PI * 1000 * t)) * 0.3;
        }

        notificationBuffer = buffer;
    }

    // Escuchar la primera interacción en el body
    document.body.addEventListener('click', initAudio, { once: true });
    document.body.addEventListener('keydown', initAudio, { once: true });
    document.body.addEventListener('touchstart', initAudio, { once: true });

    // Función principal para reproducir el sonido
    function playNotificationSound() {
        console.log('🔊 Intentando reproducir sonido...');

        // Sonido habilitado por defecto
        const soundEnabled = localStorage.getItem('chatSoundEnabled') !== 'false';
        if (!soundEnabled) {
            console.log('❌ Sonido deshabilitado por usuario');
            return;
        }

        try {
            // Inicializar audio si aún no está listo
            if (!audioContext) {
                console.log('🎵 Inicializando AudioContext...');
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                createNotificationSound();
            }

            console.log('🎵 Estado del AudioContext:', audioContext.state);

            // Reanudar el contexto si está suspendido
            if (audioContext.state === 'suspended') {
                console.log('⏸️ AudioContext suspendido, reanudando...');
                audioContext.resume().then(() => {
                    console.log('▶️ AudioContext reanudado, reproduciendo...');
                    playSound();
                }).catch(err => {
                    console.error('❌ No se pudo reanudar el audio:', err);
                });
            } else {
                console.log('▶️ Reproduciendo sonido directamente...');
                playSound();
            }
        } catch (e) {
            console.error('❌ Error en la reproducción de audio:', e);
        }
    }

    // Función auxiliar para reproducir el sonido
    function playSound() {
        if (notificationBuffer && audioContext) {
            console.log('✅ Buffer y contexto disponibles, creando source...');
            const source = audioContext.createBufferSource();
            const gainNode = audioContext.createGain();

            source.buffer = notificationBuffer;
            source.connect(gainNode);
            gainNode.connect(audioContext.destination);

            // Volumen ajustable
            gainNode.gain.value = 0.5;

            source.start(0);
            console.log('✅ Sonido reproducido exitosamente');
        } else {
            console.error('❌ Buffer o contexto no disponible:', {
                buffer: !!notificationBuffer,
                context: !!audioContext
            });
        }
    }

    // Escuchar eventos de nuevos mensajes
    window.addEventListener('new-chat-message', event => {
        console.log('🔔 Nuevo mensaje recibido, reproduciendo sonido...');

        const isChatPage = window.location.pathname.includes('/chat-interno');
        const selectedUserId = document.getElementById('chat-selected-user-id')?.value;

        const data = event.detail[0]; // Livewire 3 wraps detail in array

        // Sonido - SIEMPRE reproducir, sin importar dónde esté el usuario
        playNotificationSound();

        // Mostrar Toast solo si no estamos viendo ese chat activo
        if (isChatPage && parseInt(selectedUserId) === data.sender_id) {
            // El chat activo lo manejará y marcará como leído
            return;
        }

        const toastId = 'chat-toast-' + data.id;
        const avatarHtml = data.avatar
            ? `<img src="${data.avatar}" alt="Avatar" class="rounded-circle me-2" width="30" height="30">`
            : `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">${data.sender.charAt(0)}</div>`;

                const toastHtml = `
            <div id="${toastId}" class="toast align-items-center border-0 mb-2 shadow-sm" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000" style="background-color: var(--bs-body-bg); border-left: 4px solid var(--bs-primary) !important;">
                <div class="toast-header border-bottom-0 pb-0">
                    ${avatarHtml}
                    <strong class="me-auto text-primary">${data.sender}</strong>
                    <small class="text-muted">${data.time}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body pt-1 cursor-pointer" onclick="Livewire.dispatch('openReplyModal', { userId: ${data.sender_id} }); const toast = bootstrap.Toast.getInstance(document.getElementById('${toastId}')); if(toast) toast.hide();">
                    <span class="text-body">${data.message}</span>
                    <div class="mt-2 text-primary small"><i class="ri ri-reply-line"></i> Haz clic para responder</div>
                </div>
            </div>
        `;

        // Create container if it doesn't exist
        let container = document.getElementById('chat-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'chat-toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3 mt-5';
            container.style.zIndex = '1090'; // Encima del navbar
            document.body.appendChild(container);
        }

        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();

        // Disparar evento para actualizar contadores locales si estamos en la vista de chat
        window.dispatchEvent(new CustomEvent('update-chat-unread-counts'));
    });

    // Control de la Ventana Flotante de Chat
    let chatWindow = null;
    let isDragging = false;
    let currentX, currentY, initialX, initialY;
    let xOffset = 0, yOffset = 0;
    let floatingUnreadCount = 0;
    let isMinimized = false;

    window.addEventListener('show-reply-modal', event => {
        chatWindow = document.getElementById('chatFloatingWindow');
        if (chatWindow) {
            chatWindow.style.display = 'flex';
            chatWindow.classList.remove('minimized');
            isMinimized = false;
            floatingUnreadCount = 0;
            updateFloatingBadge();

            // Enfocar el input
            setTimeout(() => {
                const input = document.getElementById('chatFloatingInput');
                if (input) input.focus();

                // Scroll al final del chat
                const body = document.getElementById('chatFloatingBody');
                if (body) body.scrollTop = body.scrollHeight;
            }, 100);
        }
    });

    window.addEventListener('hide-reply-modal', event => {
        chatWindow = document.getElementById('chatFloatingWindow');
        if (chatWindow) {
            chatWindow.style.display = 'none';
            isMinimized = false;
            floatingUnreadCount = 0;
            updateFloatingBadge();
        }
    });

    // Cerrar ventana
    document.addEventListener('click', function(e) {
        if (e.target.closest('#closeChat')) {
            const chatWindow = document.getElementById('chatFloatingWindow');
            if (chatWindow) {
                chatWindow.style.display = 'none';
                isMinimized = false;
                floatingUnreadCount = 0;
                updateFloatingBadge();
            }
        }
    });

    // Minimizar/Maximizar ventana
    document.addEventListener('click', function(e) {
        if (e.target.closest('#minimizeChat')) {
            const chatWindow = document.getElementById('chatFloatingWindow');
            const icon = document.getElementById('minimizeIcon');
            if (chatWindow) {
                chatWindow.classList.toggle('minimized');
                isMinimized = chatWindow.classList.contains('minimized');

                // Cambiar icono
                if (isMinimized) {
                    icon.className = 'ri-arrow-up-s-line text-white';
                } else {
                    icon.className = 'ri-subtract-line text-white';
                    // Resetear contador al maximizar
                    floatingUnreadCount = 0;
                    updateFloatingBadge();
                    Livewire.dispatch('resetFloatingCount');

                    // Scroll al final
                    setTimeout(() => {
                        const body = document.getElementById('chatFloatingBody');
                        if (body) body.scrollTop = body.scrollHeight;
                    }, 100);
                }
            }
        }
    });

    // Actualizar badge de mensajes no leídos
    function updateFloatingBadge() {
        const badge = document.getElementById('floatingBadge');
        if (badge) {
            if (floatingUnreadCount > 0 && isMinimized) {
                badge.textContent = floatingUnreadCount > 99 ? '99+' : floatingUnreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Escuchar nuevos mensajes en la ventana flotante
    window.addEventListener('new-floating-message', event => {
        if (isMinimized) {
            const data = event.detail[0];
            floatingUnreadCount += data.count || 1;
            updateFloatingBadge();

            // Reproducir sonido
            playNotificationSound();
        }
    });

    // Hacer la ventana arrastrable
    document.addEventListener('mousedown', function(e) {
        const header = e.target.closest('.chat-floating-header');
        if (header && !e.target.closest('button')) {
            chatWindow = document.getElementById('chatFloatingWindow');
            if (!chatWindow) return;

            isDragging = true;
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;

            chatWindow.style.transition = 'none';
        }
    });

    document.addEventListener('mousemove', function(e) {
        if (isDragging && chatWindow) {
            e.preventDefault();
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;
            xOffset = currentX;
            yOffset = currentY;

            chatWindow.style.transform = `translate(${currentX}px, ${currentY}px)`;
        }
    });

    document.addEventListener('mouseup', function(e) {
        if (isDragging && chatWindow) {
            isDragging = false;
            chatWindow.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        }
    });

    // Soporte táctil para dispositivos móviles
    document.addEventListener('touchstart', function(e) {
        const header = e.target.closest('.chat-floating-header');
        if (header && !e.target.closest('button')) {
            chatWindow = document.getElementById('chatFloatingWindow');
            if (!chatWindow) return;

            isDragging = true;
            const touch = e.touches[0];
            initialX = touch.clientX - xOffset;
            initialY = touch.clientY - yOffset;

            chatWindow.style.transition = 'none';
        }
    });

    document.addEventListener('touchmove', function(e) {
        if (isDragging && chatWindow) {
            e.preventDefault();
            const touch = e.touches[0];
            currentX = touch.clientX - initialX;
            currentY = touch.clientY - initialY;
            xOffset = currentX;
            yOffset = currentY;

            chatWindow.style.transform = `translate(${currentX}px, ${currentY}px)`;
        }
    });

    document.addEventListener('touchend', function(e) {
        if (isDragging && chatWindow) {
            isDragging = false;
            chatWindow.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        }
    });

    // Auto-scroll cuando llegan nuevos mensajes
    Livewire.hook('morph.updated', ({ el }) => {
        if (el.id === 'chatFloatingBody') {
            // Solo hacer scroll si no está minimizado
            if (!isMinimized) {
                setTimeout(() => {
                    el.scrollTop = el.scrollHeight;
                }, 100);
            }
        }
    });

    // Scroll al enviar mensaje
    window.addEventListener('message-sent', () => {
        const body = document.getElementById('chatFloatingBody');
        if (body && !isMinimized) {
            setTimeout(() => {
                body.scrollTop = body.scrollHeight;
            }, 100);
        }
    });
});
</script>
@endpush

</div>
