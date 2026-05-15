<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row g-0">
    <!-- Left Section - Full Background Image -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 position-relative overflow-hidden">
      <img
        src="{{ asset('fondo/fondo.jpeg') }}"
        class="position-absolute w-100 h-100"
        style="object-fit: cover; top: 0; left: 0; right: 0; bottom: 0;"
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

        @if($successMessage)
          <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="ri ri-whatsapp-line fs-5"></i>
            <div>{{ $successMessage }}</div>
          </div>

          <div class="text-center">
            <a href="{{ route('login') }}" class="btn btn-primary w-100">
              <i class="ri ri-arrow-left-line me-1"></i>Volver al inicio de sesión
            </a>
          </div>
        @else
          <h4 class="mb-1">{{ __('auth_ui.forgot_password_title') }} 🔒</h4>
          <p class="mb-5">{{ __('auth_ui.forgot_password_subtitle') }}</p>

          <form wire:submit="sendResetLink">
            <div class="form-floating form-floating-outline mb-3 form-control-validation">
              <input
                type="text"
                class="form-control @if($hasError('identifier')) is-invalid @endif"
                id="identifier"
                wire:model.live.debounce.300ms="identifier"
                placeholder="Ej: 04121234567"
                autofocus
                inputmode="tel" />
              <label for="identifier">{{ __('auth_ui.email_or_phone') }}</label>
              @if($hasError('identifier'))
                <div class="invalid-feedback d-block">
                  <i class="ri ri-error-warning-line me-1"></i>{{ $getError('identifier') }}
                </div>
              @endif
            </div>

            <div class="alert alert-info py-2 mb-4 d-flex align-items-center gap-2">
              <i class="ri ri-whatsapp-line fs-5 text-success"></i>
              <small>Recibirás un enlace de recuperación por <strong>WhatsApp</strong> válido por <strong>15 minutos</strong>.</small>
            </div>

            <div class="mb-5">
              <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="sendResetLink">
                  <i class="ri ri-whatsapp-line me-1"></i>{{ __('auth_ui.send_reset_link') }}
                </span>
                <span wire:loading>
                  <span class="spinner-border spinner-border-sm me-2"></span>
                  Enviando...
                </span>
              </button>
            </div>
          </form>

          <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
              <i class="icon-base ri ri-arrow-left-s-line"></i>
              {{ __('auth_ui.back_to_login') }}
            </a>
          </div>
        @endif

      </div>
    </div>
    <!-- /Right Section -->
  </div>
</div>
