<div>
    <a class="nav-link btn btn-text-secondary rounded-pill btn-icon hide-arrow" href="{{ route('admin.chat-interno.index') }}">
    <i class="ri ri-chat-1-line fs-4"></i>
    @if($unreadCount > 0)
        <span class="position-absolute top-0 start-50 translate-middle-y badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem; margin-top: 10px;">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</a>

<div wire:poll.3s="checkNewMessages" class="d-none"></div>

<!-- Elementos de audio ocultos en el DOM para esquivar la política Autoplay de algunos navegadores -->
<audio id="chat-notification-audio" src="{{ asset('sounds/notification.mp3') }}" preload="auto" class="d-none"></audio>
<audio id="chat-fallback-audio" src="{{ asset('sounds/beep.mp3') }}" preload="auto" class="d-none"></audio>

<!-- Modal de Respuesta Rápida -->
<div wire:ignore.self class="modal fade" id="chatReplyModal" tabindex="-1" aria-labelledby="chatReplyModalLabel" aria-hidden="true" style="z-index: 3000;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 ">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title mb-0 text-white" id="chatReplyModalLabel">
                    <i class="ri-chat-1-line me-2"></i> Responder a {{ $replyingToUser->name ?? '...' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background-color: var(--bs-body-bg);">
                <!-- Contexto de mensajes recientes -->
                <div class="p-4 overflow-auto" style="height: 400px; max-height: 60vh; background-image: url('{{ asset('img/illustrations/chat-bg.png') }}'); background-size: cover; background-color: rgba(255,255,255,0.9); background-blend-mode: overlay;">
                    @if(!empty($recentMessages))
                        @foreach($recentMessages as $msg)
                            @php $isMine = $msg['sender_id'] === auth()->id(); @endphp
                            <div class="d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                                <div class="px-4 py-2 rounded-3 shadow-sm" style="max-width: 80%; font-size: 1rem; {{ $isMine ? 'background-color: var(--bs-primary); color: white; border-bottom-right-radius: 0 !important;' : 'background-color: white; color: var(--bs-body-color); border-bottom-left-radius: 0 !important;' }}">
                                    {{ $msg['message'] }}
                                    <div class="text-end mt-1" style="font-size: 0.75rem; opacity: 0.8;">
                                        {{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">Cargando contexto...</div>
                    @endif
                </div>

                <!-- Input de Respuesta -->
                <div class="p-3 border-top bg-white">
                    <form wire:submit.prevent="sendReply">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control border-end-0 fs-6" wire:model="replyMessage" placeholder="Escribe tu mensaje aquí..." autocomplete="off" autofocus>
                            <button class="btn btn-outline-primary border-start-0 px-4" type="submit" wire:loading.attr="disabled" wire:target="sendReply">
                                <span wire:loading.remove wire:target="sendReply"><i class="ri-send-plane-fill fs-5"></i></span>
                                <span wire:loading wire:target="sendReply"><i class="ri-loader-4-line ri-spin fs-5"></i></span>
                            </button>
                        </div>
                        @error('replyMessage') <span class="text-danger mt-2 d-block">{{ $message }}</span> @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const notifAudio = document.getElementById('chat-notification-audio');
    const fallbackAudio = document.getElementById('chat-fallback-audio');

    // Función para "desbloquear" el audio en la primera interacción del usuario en toda la página
    const unlockAudio = () => {
        if(notifAudio) {
            notifAudio.play().then(() => {
                notifAudio.pause();
                notifAudio.currentTime = 0;
            }).catch(() => {});
        }

        if(fallbackAudio) {
            fallbackAudio.play().then(() => {
                fallbackAudio.pause();
                fallbackAudio.currentTime = 0;
            }).catch(() => {});
        }

        // Una vez desbloqueado, removemos los event listeners para no sobrecargar
        document.body.removeEventListener('click', unlockAudio);
        document.body.removeEventListener('keydown', unlockAudio);
        document.body.removeEventListener('touchstart', unlockAudio);
    };

    // Escuchar la primera interacción en el body
    document.body.addEventListener('click', unlockAudio, { once: true });
    document.body.addEventListener('keydown', unlockAudio, { once: true });
    document.body.addEventListener('touchstart', unlockAudio, { once: true });

    // Función principal para reproducir el sonido
    function playNotificationSound() {
        // Solo reproducir si está habilitado en localStorage
        const soundEnabled = localStorage.getItem('chatSoundEnabled') !== 'false';
        if (!soundEnabled) return;

        try {
            if(notifAudio) {
                notifAudio.volume = 1.0;
                notifAudio.currentTime = 0; // Reiniciar por si ya estaba sonando

                const playPromise = notifAudio.play();

                if (playPromise !== undefined) {
                    playPromise.catch(e => {
                        console.log('Audio principal bloqueado. Intentando fallback...', e);
                        if(fallbackAudio) {
                            fallbackAudio.volume = 1.0;
                            fallbackAudio.currentTime = 0;
                            fallbackAudio.play().catch(err => {
                                console.log('El navegador bloqueó completamente el audio automático (Autoplay Policy).', err);
                            });
                        }
                    });
                }
            }
        } catch (e) {
            console.log('Error en la reproducción de audio', e);
        }
    }

    // Escuchar eventos de nuevos mensajes
    window.addEventListener('new-chat-message', event => {
        const isChatPage = window.location.pathname.includes('/chat-interno');
        const selectedUserId = document.getElementById('chat-selected-user-id')?.value;

        const data = event.detail[0]; // Livewire 3 wraps detail in array

        // Sonido
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
                <div class="toast-body pt-1 cursor-pointer" onclick="Livewire.dispatch('openReplyModal', { userId: ${data.sender_id} }); bootstrap.Toast.getInstance(document.getElementById('${toastId}')).hide();">
                    <span class="text-body">${data.message}</span>
                    <div class="mt-2 text-primary small"><i class="ri-reply-line"></i> Haz clic para responder rápido</div>
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

    // Control del Modal de Respuesta Rápida
    window.addEventListener('show-reply-modal', event => {
        const modalEl = document.getElementById('chatReplyModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            // Enfocar el input después de abrir el modal
            setTimeout(() => {
                const input = modalEl.querySelector('input[wire\\:model="replyMessage"]');
                if (input) input.focus();
            }, 500);

            // Hacer scroll hacia abajo en el contexto
            const contextDiv = modalEl.querySelector('.overflow-auto');
            if (contextDiv) {
                setTimeout(() => contextDiv.scrollTop = contextDiv.scrollHeight, 100);
            }
        }
    });

    window.addEventListener('hide-reply-modal', event => {
        const modalEl = document.getElementById('chatReplyModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    });
});
</script>
@endpush

</div>
