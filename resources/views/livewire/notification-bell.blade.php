<div wire:poll.10s="$refresh">
    <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
       href="javascript:void(0);"
       data-bs-toggle="dropdown"
       data-bs-auto-close="outside"
       aria-expanded="false"
       aria-label="{{ __('messages.notifications') }}{{ $unreadCount > 0 ? ', ' . $unreadCount . ' ' . __('messages.unread') : '' }}">
        <i class="icon-base ri ri-notification-2-line icon-22px" aria-hidden="true"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border"></span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end py-0" role="menu" aria-label="{{ __('messages.notifications') }}">
        <li class="dropdown-menu-header border-bottom py-3">
            <div class="dropdown-header d-flex align-items-center py-2">
                <h6 class="mb-0 me-auto">{{ __('messages.notifications') }}</h6>
                <div class="d-flex align-items-center h6 mb-0">
                    @if($unreadCount > 0)
                        <span class="badge rounded-pill bg-label-primary fs-xsmall me-2" aria-live="polite">
                            {{ $unreadCount }} Nuevas
                        </span>
                    @endif
                    <a href="javascript:void(0)"
                       wire:click="markAllAsRead"
                       class="dropdown-notifications-all p-2"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ __('messages.mark_all_read') }}"
                       aria-label="{{ __('messages.mark_all_read') }}">
                        <i class="icon-base ri ri-mail-open-line text-heading" aria-hidden="true"></i>
                    </a>
                </div>

@push('styles')
<style>
    .dropdown-notifications-item.marked-as-read {
        opacity: 0.7;
    }

    .dropdown-notifications-item.marked-as-read .text-body {
        color: #6c757d !important;
    }

    .dropdown-notifications-item.marked-as-read .text-body-secondary {
        color: #adb5bd !important;
    }
</style>
@endpush
            </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    <li class="list-group-item list-group-item-action dropdown-notifications-item {{ $notification->isRead() ? 'marked-as-read' : '' }}"
                        id="notification-{{ $notification->id }}"
                        onclick="markNotificationAsRead({{ $notification->id }})"
                        role="button"
                        tabindex="0"
                        aria-label="{{ $notification->title }}: {{ $notification->message }}. {{ $notification->created_at->diffForHumans() }}">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                @if($notification->icon_type === 'image' && $notification->avatar)
                                    <div class="avatar">
                                        <img src="{{ $notification->avatar }}" alt="avatar" class="w-px-40 h-auto rounded-circle">
                                    </div>
                                @elseif($notification->icon_type === 'initials')
                                    <div class="avatar">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $notification->type }}">
                                            {{ $notification->avatar }}
                                        </span>
                                    </div>
                                @elseif($notification->icon_type === 'initials-icon' && $notification->icon)
                                    <div class="avatar">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $notification->type }}">
                                            <i class="icon-base ri {{ $notification->icon }} icon-18px" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                @else
                                    <div class="avatar">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $notification->type }}">
                                            <i class="icon-base ri {{ $notification->icon }} icon-18px" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="small mb-1">{{ $notification->title }}</h6>
                                <small class="mb-1 d-block text-body">{{ $notification->message }}</small>
                                <small class="text-body-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                @if($notification->isUnread())
                                    <span id="unread-indicator-{{ $notification->id }}" class="badge badge-dot bg-danger"
                                          aria-label="Unread notification"></span>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center py-4">
                        <i class="icon-base ri ri-notification-off-line icon-48px text-body mb-2" aria-hidden="true"></i>
                        <p class="text-body mb-0">No hay notificaciones pendientes</p>
                    </li>
                @endforelse
            </ul>
        </li>
        <li class="border-top">
            <div class="d-grid p-4">
                <a class="btn btn-primary btn-sm d-flex" href="{{ route('admin.notifications.index') }}" aria-label="Ver todas las notificaciones">
                    <small class="align-middle">Ver todas las notificaciones</small>
                </a>
            </div>
        </li>
    </ul>
</div>

<script>
// Sistema de sonido para notificaciones (Web Audio API)
let notificationAudioContext = null;
let notificationSoundBuffer = null;

// Inicializar AudioContext en la primera interacción del usuario
const initNotificationAudio = () => {
    if (!notificationAudioContext) {
        notificationAudioContext = new (window.AudioContext || window.webkitAudioContext)();
        createNotificationSound();
    }
    document.body.removeEventListener('click', initNotificationAudio);
    document.body.removeEventListener('keydown', initNotificationAudio);
    document.body.removeEventListener('touchstart', initNotificationAudio);
};

// Crear sonido de notificación usando Web Audio API
function createNotificationSound() {
    if (!notificationAudioContext) return;

    const sampleRate = notificationAudioContext.sampleRate;
    const duration = 0.3; // 300ms
    const buffer = notificationAudioContext.createBuffer(1, sampleRate * duration, sampleRate);
    const data = buffer.getChannelData(0);

    // Generar un tono agradable (dos tonos: 800Hz y 1000Hz)
    for (let i = 0; i < buffer.length; i++) {
        const t = i / sampleRate;
        const envelope = Math.exp(-3 * t); // Decay exponencial
        data[i] = envelope * (Math.sin(2 * Math.PI * 800 * t) + Math.sin(2 * Math.PI * 1000 * t)) * 0.3;
    }

    notificationSoundBuffer = buffer;
}

// Reproducir sonido de notificación
function playNotificationBeep() {
    console.log('🔔 Intentando reproducir sonido de notificación...');

    // Sonido habilitado por defecto
    const soundEnabled = localStorage.getItem('notificationSoundEnabled') !== 'false';
    if (!soundEnabled) {
        console.log('❌ Sonido de notificación deshabilitado por usuario');
        return;
    }

    try {
        // Inicializar audio si aún no está listo
        if (!notificationAudioContext) {
            console.log('🎵 Inicializando AudioContext para notificaciones...');
            notificationAudioContext = new (window.AudioContext || window.webkitAudioContext)();
            createNotificationSound();
        }

        console.log('🎵 Estado del AudioContext:', notificationAudioContext.state);

        // Reanudar el contexto si está suspendido
        if (notificationAudioContext.state === 'suspended') {
            console.log('⏸️ AudioContext suspendido, reanudando...');
            notificationAudioContext.resume().then(() => {
                console.log('▶️ AudioContext reanudado, reproduciendo beep...');
                playBeepSound();
            }).catch(err => {
                console.error('❌ No se pudo reanudar el audio:', err);
            });
        } else {
            console.log('▶️ Reproduciendo beep directamente...');
            playBeepSound();
        }
    } catch (e) {
        console.error('❌ Error en la reproducción de audio:', e);
    }
}

// Función auxiliar para reproducir el sonido
function playBeepSound() {
    if (!notificationAudioContext || !notificationSoundBuffer) {
        console.warn('⚠️ AudioContext o buffer no están listos');
        return;
    }

    try {
        const source = notificationAudioContext.createBufferSource();
        source.buffer = notificationSoundBuffer;
        source.connect(notificationAudioContext.destination);
        source.start(0);
        console.log('✅ Beep de notificación reproducido exitosamente');
    } catch (e) {
        console.error('❌ Error al reproducir beep:', e);
    }
}

// Escuchar la primera interacción en el body
document.body.addEventListener('click', initNotificationAudio, { once: true });
document.body.addEventListener('keydown', initNotificationAudio, { once: true });
document.body.addEventListener('touchstart', initNotificationAudio, { once: true });

// Escuchar evento de nueva notificación desde Livewire y reproducir beep
window.addEventListener('notification-created', () => {
    console.log('🔔 Evento notification-created recibido - reproduciendo beep...');
    setTimeout(() => {
        playNotificationBeep();
    }, 200);
});

function markNotificationAsRead(notificationId) {
    const notification = document.getElementById('notification-' + notificationId);
    const badge = document.querySelector('.badge.badge-dot.bg-danger');
    const countBadge = document.querySelector('.badge.rounded-pill.bg-label-primary');

    // Remove notification from list with animation
    if (notification) {
        notification.style.transition = 'opacity 0.3s ease';
        notification.style.opacity = '0';

        setTimeout(() => {
            notification.remove();

            // Check if there are any notifications left
            const remainingNotifications = document.querySelectorAll('.dropdown-notifications-item:not([style*="opacity: 0"])');

            if (remainingNotifications.length === 0) {
                // Show empty state
                const notificationsList = document.querySelector('.dropdown-notifications-list .list-group');
                if (notificationsList) {
                    notificationsList.innerHTML = `
                        <li class="list-group-item text-center py-4">
                            <i class="ri ri-notification-off-line ri-48px text-body mb-2"></i>
                            <p class="text-body mb-0">No hay notificaciones pendientes</p>
                        </li>
                    `;
                }

                // Remove badges
                if (badge) badge.remove();
                if (countBadge) countBadge.remove();
            } else {
                // Update count
                const newCount = remainingNotifications.length;
                if (countBadge) {
                    countBadge.textContent = newCount + ' Nuevas';
                }
            }
        }, 300);
    }

    // Call Livewire method
    @this.call('markAsRead', notificationId);
}
</script>
