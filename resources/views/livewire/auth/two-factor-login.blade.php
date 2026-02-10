<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <!-- 2FA Verification -->
      <div class="card p-md-7 p-1">
        <!-- Logo -->
         @include('auth.header.logo')
        <!-- /Logo -->

        <div class="card-body mt-1">
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
                  wire:model="code"
                  placeholder="{{ __('auth_ui.two_factor_code') }}"
                  autofocus
                  maxlength="6"
                  inputmode="numeric"
                  pattern="[0-9]*" />
                <label for="code">{{ __('auth_ui.two_factor_code') }}</label>
              </div>
              <div class="form-text">{{ __('auth_ui.two_factor_subtitle') }}</div>
            </div>

            <div class="mb-5">
              <button class="btn btn-primary d-grid w-100" type="submit">{{ __('auth_ui.verify_2fa') }}</button>
            </div>
          </form>

          <div class="text-center">
            <a href="{{ route('login') }}">{{ __('auth_ui.back_to_login') }}</a>
          </div>
        </div>
      </div>
      <!-- /2FA Verification -->
    </div>
  </div>
</div>

@push('scripts')
  <script>
    document.addEventListener('livewire:initialized', () => {
      // Al hacer clic en el botón
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            // Si tiene éxito, emite un evento con las coordenadas
            @this.latitude = position.coords.latitude;
            @this.longitude = position.coords.longitude;
          },
          (error) => {
            // Si hay un error, emite un evento con el mensaje de error
            @this.dispatch('setError', {
              error: error.message
            });
          }
        );
      } else {
        // El navegador no soporta la geolocalización
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