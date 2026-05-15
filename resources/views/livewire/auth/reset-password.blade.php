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
              wire:model.live.debounce.300ms="email"
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
              wire:model.live.debounce.300ms="token"
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
                    wire:model.live.debounce.300ms="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <label for="password">{{ __('auth_ui.new_password') }}</label>
                  @if($hasError('password'))
                    <div class="invalid-feedback d-block">{{ $getError('password') }}</div>
                  @endif
                </div>
                <span class="input-group-text cursor-pointer" onclick="togglePassword('password', 'passwordIcon1')">
                  <i class="icon-base ri ri-eye-off-line icon-20px" id="passwordIcon1"></i>
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
                    wire:model.live.debounce.300ms="password_confirmation"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <label for="password_confirmation">{{ __('auth_ui.confirm_password') }}</label>
                  @if($hasError('password_confirmation'))
                    <div class="invalid-feedback d-block">{{ $getError('password_confirmation') }}</div>
                  @endif
                </div>
                <span class="input-group-text cursor-pointer" onclick="togglePassword('password_confirmation', 'passwordIcon2')">
                  <i class="icon-base ri ri-eye-off-line icon-20px" id="passwordIcon2"></i>
                </span>
              </div>
            </div>
          </div>

          <div class="mb-5">
            <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
              <span wire:loading.remove>{{ __('auth_ui.reset_button') }}</span>
              <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2"></span>
                Restableciendo...
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
    <!-- /Right Section -->
  </div>
</div>

@push('scripts')
<script>
  function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const passwordIcon = document.getElementById(iconId);

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      passwordIcon.classList.remove('ri-eye-off-line');
      passwordIcon.classList.add('ri-eye-line');
    } else {
      passwordInput.type = 'password';
      passwordIcon.classList.remove('ri-eye-line');
      passwordIcon.classList.add('ri-eye-off-line');
    }
  }
</script>
@endpush
