<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <div class="card p-md-7 p-1">
        @include('auth.header.logo')

        <div class="card-body mt-1">
          <h4 class="mb-1">{{ __('auth_ui.reset_password_title') }} 🔒</h4>
          <p class="mb-5">Ingresa el código de 6 dígitos que recibiste por WhatsApp y tu nueva contraseña.</p>

          @if(session()->has('status'))
            <div class="alert alert-success" role="alert">{{ session('status') }}</div>
          @endif

          @if($successMessage)
            <div class="alert alert-success" role="alert">{{ $successMessage }}</div>
          @endif

          <form wire:submit="resetPassword">
            @csrf
            <input type="hidden" wire:model="token">

            {{-- Email --}}
            <div class="form-floating form-floating-outline mb-4 form-control-validation">
              <input
                type="email"
                class="form-control @if($hasError('email')) is-invalid @endif"
                id="email"
                wire:model="email"
                placeholder="{{ __('auth_ui.email') }}"
                autofocus />
              <label for="email">{{ __('auth_ui.email') }}</label>
              @if($hasError('email'))
                <div class="invalid-feedback d-block">{{ $getError('email') }}</div>
              @endif
            </div>

            {{-- Código de 6 dígitos --}}
            <div class="form-floating form-floating-outline mb-4 form-control-validation">
              <input
                type="text"
                class="form-control @if($hasError('token')) is-invalid @endif"
                id="token"
                wire:model="token"
                placeholder="000000"
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                style="letter-spacing:.5rem;font-size:1.4rem;text-align:center;" />
              <label for="token">Código de verificación (6 dígitos)</label>
              @if($hasError('token'))
                <div class="invalid-feedback d-block">{{ $getError('token') }}</div>
              @endif
            </div>

            {{-- Nueva contraseña --}}
            <div class="mb-4">
              <div class="form-password-toggle form-control-validation">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input
                      type="password"
                      id="password"
                      class="form-control @if($hasError('password')) is-invalid @endif"
                      wire:model="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                    <label for="password">{{ __('auth_ui.new_password') }}</label>
                    @if($hasError('password'))
                      <div class="invalid-feedback d-block">{{ $getError('password') }}</div>
                    @endif
                  </div>
                  <span class="input-group-text cursor-pointer">
                    <i class="icon-base ri ri-eye-off-line icon-20px"></i>
                  </span>
                </div>
              </div>
            </div>

            {{-- Confirmar contraseña --}}
            <div class="mb-5">
              <div class="form-password-toggle form-control-validation">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input
                      type="password"
                      id="password_confirmation"
                      class="form-control @if($hasError('password_confirmation')) is-invalid @endif"
                      wire:model="password_confirmation"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                    <label for="password_confirmation">{{ __('auth_ui.confirm_password') }}</label>
                    @if($hasError('password_confirmation'))
                      <div class="invalid-feedback d-block">{{ $getError('password_confirmation') }}</div>
                    @endif
                  </div>
                  <span class="input-group-text cursor-pointer">
                    <i class="icon-base ri ri-eye-off-line icon-20px"></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="mb-5">
              <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ __('auth_ui.reset_button') }}</span>
                <span wire:loading>
                  <span class="spinner-border spinner-border-sm me-1"></span>Procesando...
                </span>
              </button>
            </div>
          </form>

          <div class="text-center">
            <a href="{{ route('password.request') }}" class="d-flex align-items-center justify-content-center">
              <i class="icon-base ri ri-arrow-left-s-line"></i>
              Solicitar nuevo código
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
