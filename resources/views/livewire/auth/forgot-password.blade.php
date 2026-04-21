<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <div class="card p-md-7 p-1">
        @include('auth.header.logo')

        <div class="card-body mt-1">
          <h4 class="mb-1">{{ __('auth_ui.forgot_password_title') }} 🔒</h4>
          <p class="mb-5">{{ __('auth_ui.forgot_password_subtitle') }}</p>

          @if($successMessage)
            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
              <i class="ri ri-whatsapp-line fs-5"></i>
              <div>{{ $successMessage }}</div>
            </div>
            <div class="text-center mt-3">
              <a href="{{ route('login') }}" class="btn btn-primary w-100">
                <i class="ri ri-arrow-left-line me-1"></i>Volver al inicio de sesión
              </a>
            </div>
          @else
            <form wire:submit="sendResetLink">

              <div class="form-floating form-floating-outline mb-3 form-control-validation">
                <input
                  type="text"
                  class="form-control @if($hasError('identifier')) is-invalid @endif"
                  id="identifier"
                  wire:model="identifier"
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
    </div>
  </div>
</div>
