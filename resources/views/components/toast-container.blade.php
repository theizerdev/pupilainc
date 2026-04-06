{{-- Contenedor de Toasts Global --}}
<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999 !important; pointer-events: none;">
  {{-- Los toasts se añadirán dinámicamente aquí --}}
</div>

<style>
/* Estilos adicionales para asegurar visibilidad de los toasts */
.toast-container {
  position: fixed !important;
  top: 20px !important;
  right: 20px !important;
  max-width: 350px !important;
}

.toast {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
  border: none !important;
  margin-bottom: 1rem !important;
}

/* Asegurar que los toasts estén por encima de todo */
.toast-container * {
  z-index: 10000 !important;
}
</style>

@push('scripts')
<script>
// Verificar mensajes flash de sesión al cargar
document.addEventListener('DOMContentLoaded', function() {
    @if(session()->has('success'))
        window.showToast('success', "{{ session('success') }}");
    @endif

    @if(session()->has('error'))
        window.showToast('error', "{{ session('error') }}");
    @endif

    @if(session()->has('warning'))
        window.showToast('warning', "{{ session('warning') }}");
    @endif

    @if(session()->has('info'))
        window.showToast('info', "{{ session('info') }}");
    @endif
});

// Función para crear y mostrar un toast
window.showToast = function(type, message, duration = 5000) {
  if (!type || typeof type !== 'string') {
    console.warn('showToast called with invalid type:', type);
    return;
  }

  // Esperar a que el DOM esté listo y Bootstrap disponible
  function initToast() {
    const toastContainer = document.getElementById('global-toast-container');
    if (!toastContainer) {
      console.warn('Toast container no encontrado');
      return;
    }

    // Verificar que Bootstrap Toast esté disponible
    if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
      console.warn('Bootstrap Toast no está disponible');
      // Fallback: mostrar alerta temporal
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
      alertDiv.style.zIndex = '9999';
      alertDiv.innerHTML = `
        <strong>${type.charAt(0).toUpperCase() + type.slice(1)}:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.body.appendChild(alertDiv);
      
      setTimeout(() => {
        if (alertDiv.parentNode) {
          alertDiv.parentNode.removeChild(alertDiv);
        }
      }, duration);
      return;
    }

    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    
    // Determinar el color y el icono según el tipo
    let toastClass, iconClass;
    switch(type) {
      case 'success':
        toastClass = 'bg-success text-white';
        iconClass = 'ri-check-line';
        break;
      case 'error':
        toastClass = 'bg-danger text-white';
        iconClass = 'ri-close-line';
        break;
      case 'warning':
        toastClass = 'bg-warning text-dark';
        iconClass = 'ri-alert-line';
        break;
      case 'info':
        toastClass = 'bg-info text-white';
        iconClass = 'ri-information-line';
        break;
      default:
        toastClass = 'bg-light text-dark';
        iconClass = 'ri-notification-line';
    }

    const toastHTML = `
      <div id="${toastId}" class="toast ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${duration}" style="pointer-events: auto;">
        <div class="toast-header ${toastClass}">
          <i class="${iconClass} me-2"></i>
          <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Inicializar y mostrar el toast de Bootstrap
    try {
      const toastElement = document.getElementById(toastId);
      const toast = new bootstrap.Toast(toastElement);
      toast.show();

      // Eliminar el toast del DOM después de que se oculte
      toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
      });
    } catch (error) {
      console.error('Error al mostrar toast:', error);
    }
  }

  // Ejecutar inmediatamente o esperar al DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initToast);
  } else {
    initToast();
  }
};

// Listener para eventos de Livewire
if (typeof Livewire !== 'undefined') {
  Livewire.on('showToast', function(data) {
    // Si data es un array con un objeto, extraer el primer elemento
    const toastData = Array.isArray(data) && data.length > 0 ? data[0] : data;
    if (toastData && toastData.type && toastData.message) {
      window.showToast(toastData.type, toastData.message, toastData.duration || 5000);
    }
  });
}

// Listener para eventos de Alpine.js (si se usa)
if (typeof Alpine !== 'undefined') {
  window.addEventListener('show-toast', function(event) {
    const { type, message, duration } = event.detail;
    window.showToast(type, message, duration || 5000);
  });
}

// Script adicional para asegurar que los toasts funcionen después de cargar todo
window.addEventListener('load', function() {
  console.log('Sistema de Toast cargado correctamente');
  
  // Verificar que el contenedor existe y es visible
  const container = document.getElementById('global-toast-container');
  if (container) {
    console.log('Toast container encontrado y listo');
    
    // Forzar que el contenedor esté visible
    container.style.display = 'block';
    container.style.visibility = 'visible';
    container.style.opacity = '1';
  } else {
    console.warn('Toast container no encontrado después de cargar');
  }
});

// Fallback para mostrar notificaciones incluso si hay errores
window.showToastSafe = function(type, message, duration = 5000) {
  try {
    if (typeof window.showToast === 'function') {
      window.showToast(type, message, duration);
    } else {
      // Fallback ultra-simple
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px; max-width: 400px;';
      notification.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
          <div><strong>${type.charAt(0).toUpperCase() + type.slice(1)}:</strong> ${message}</div>
          <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
      `;
      document.body.appendChild(notification);
      
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, duration);
    }
  } catch (error) {
    console.error('Error al mostrar notificación:', error);
  }
};
</script>
@endpush