<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row g-0">
    <!-- Left Section - Full Background Image -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 position-relative overflow-hidden" style="min-height: 100vh;">
      <img
        src="{{ asset('fondo/fondo.jpeg') }}"
        class="position-absolute"
        style="width: 100%; height: 100%; object-fit: cover; top: 0; left: 0; right: 0; bottom: 0;"
        alt="auth-illustration" />
    </div>
    <!-- /Left Section -->

    <!-- Right Section - Form -->
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
      <div class="w-px-400 mx-auto pt-12 pt-lg-0">
        <!-- Logo -->
        <div class="mb-5 text-center">
          @include('auth.header.logo')
        </div>
        <!-- /Logo -->

        <!-- 2FA Verification -->
        <h4 class="mb-1">{{ __('auth_ui.two_factor_title') }}</h4>
        <p class="mb-5">{{ __('auth_ui.two_factor_subtitle') }}</p>

        @if (session()->has('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <form wire:submit.prevent="verifyCode" id="twoFactorForm">
          <input type="hidden" wire:model="latitude" id="latitude">
          <input type="hidden" wire:model="longitude" id="longitude">

          <div class="mb-5">
            <div class="form-floating form-floating-outline">
              <input
                type="text"
                class="form-control"
                id="code"
                name="code"
                wire:model.live.debounce.300ms="code"
                placeholder="{{ __('auth_ui.two_factor_code') }}"
                autofocus
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]*"
                style="letter-spacing:.5rem;font-size:1.4rem;text-align:center;" />
              <label for="code">{{ __('auth_ui.two_factor_code') }}</label>
            </div>
            <div class="form-text">{{ __('auth_ui.two_factor_subtitle') }}</div>
          </div>

          <div class="mb-5">
            <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
              <span wire:loading.remove>{{ __('auth_ui.verify_2fa') }}</span>
              <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2"></span>
                Verificando...
              </span>
            </button>
          </div>
        </form>

        <div class="text-center">
          <a href="{{ route('login') }}">{{ __('auth_ui.back_to_login') }}</a>
        </div>
        <!-- /2FA Verification -->

      </div>
    </div>
    <!-- /Right Section -->
  </div>
</div>

@push('scripts')
  <script>
    document.addEventListener('livewire:initialized', () => {
      // Geolocalización
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            @this.latitude = position.coords.latitude;
            @this.longitude = position.coords.longitude;
          },
          (error) => {
            @this.dispatch('setError', {
              error: error.message
            });
          }
        );
      } else {
        @this.dispatch('setError', {
          error: "Geolocalización no es soportada por este navegador."
        });
      }

      // Manejar entrada de código con auto-focus y auto-submit
      const codeInput = document.getElementById('code');
      if (codeInput) {
        codeInput.addEventListener('input', function(e) {
          // Solo permitir números
          this.value = this.value.replace(/[^0-9]/g, '');

          // Si se ingresan 6 dígitos, enviar automáticamente
          if (this.value.length === 6) {
            @this.verifyCode();
          }
        });

        // Auto-focus en el primer campo vacío
        if (!codeInput.value) {
          codeInput.focus();
        }
      }
    });
  </script>
@endpush
