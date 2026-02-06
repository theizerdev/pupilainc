<div id="notification-container" class="notification-container">
    <!-- Las notificaciones se insertarán aquí dinámicamente -->
</div>

<style>
.notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
}

.notification {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 10px;
    padding: 16px;
    border-left: 4px solid;
    animation: slideIn 0.3s ease-out;
    position: relative;
    overflow: hidden;
}

.notification.success {
    border-left-color: #28a745;
    background-color: #f8fff9;
}

.notification.error {
    border-left-color: #dc3545;
    background-color: #fff8f8;
}

.notification.warning {
    border-left-color: #ffc107;
    background-color: #fffbf0;
}

.notification.info {
    border-left-color: #17a2b8;
    background-color: #f8fcff;
}

.notification-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 12px;
    flex-shrink: 0;
}

.notification.success .notification-icon {
    background-color: #28a745;
    color: white;
}

.notification.error .notification-icon {
    background-color: #dc3545;
    color: white;
}

.notification.warning .notification-icon {
    background-color: #ffc107;
    color: #212529;
}

.notification.info .notification-icon {
    background-color: #17a2b8;
    color: white;
}

.notification-content {
    display: flex;
    align-items: flex-start;
}

.notification-message {
    flex: 1;
    font-size: 14px;
    line-height: 1.4;
    color: #333;
}

.notification-close {
    position: absolute;
    top: 8px;
    right: 8px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #999;
    padding: 4px;
    line-height: 1;
}

.notification-close:hover {
    color: #666;
}

.notification-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background-color: currentColor;
    opacity: 0.3;
    animation: progress 3s linear forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

/* Animación de salida */
.notification.hiding {
    animation: slideOut 0.3s ease-in forwards;
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>

<script>
class NotificationSystem {
    constructor() {
        this.container = document.getElementById('notification-container');
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Escuchar eventos de Livewire
        if (typeof Livewire !== 'undefined') {
            Livewire.on('notify', (data) => {
                const notification = data[0];
                this.show(notification.type, notification.message, notification.duration || 3000);
            });
        }

        // Escuchar eventos personalizados
        document.addEventListener('show-notification', (event) => {
            const { type, message, duration } = event.detail;
            this.show(type, message, duration || 3000);
        });
    }

    show(type, message, duration = 3000) {
        const notification = this.createNotification(type, message);
        this.container.appendChild(notification);

        // Auto-cerrar después del tiempo especificado
        setTimeout(() => {
            this.hide(notification);
        }, duration);

        // Cerrar al hacer clic en la X
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            this.hide(notification);
        });

        // Cerrar al hacer clic en la notificación
        notification.addEventListener('click', (e) => {
            if (e.target === notification || e.target.closest('.notification-content')) {
                this.hide(notification);
            }
        });
    }

    createNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">${icons[type] || 'ℹ'}</div>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close">&times;</button>
            <div class="notification-progress"></div>
        `;

        return notification;
    }

    hide(notification) {
        notification.classList.add('hiding');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    // Métodos de conveniencia
    success(message, duration) {
        this.show('success', message, duration);
    }

    error(message, duration) {
        this.show('error', message, duration);
    }

    warning(message, duration) {
        this.show('warning', message, duration);
    }

    info(message, duration) {
        this.show('info', message, duration);
    }
}

// Inicializar el sistema de notificaciones cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.notificationSystem = new NotificationSystem();
});

// Función global para mostrar notificaciones
function showNotification(type, message, duration) {
    if (window.notificationSystem) {
        window.notificationSystem.show(type, message, duration);
    } else {
        console.warn('Notification system not initialized');
        alert(message);
    }
}
</script>