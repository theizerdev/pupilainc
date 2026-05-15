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

    <!-- Login Form -->
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
      <div class="w-px-400 mx-auto pt-12 pt-lg-0">
        <!-- Logo -->
        <div class="mb-5 text-center">
          @include('auth.header.logo')
        </div>
        <!-- /Logo -->

        <!-- Mensajes de sesión -->
        @if(session('info'))
          <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if(session('status'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
        <!-- /Mensajes de sesión -->

        <h4 class="mb-1">¡{{ __('auth_ui.login_title') }}s! 👋</h4>
        <p class="mb-5">{{ __('auth_ui.login_subtitle') }}</p>

        <form wire:submit.prevent="authenticate" id="loginForm" class="mb-5">
          <input type="hidden" wire:model="latitude" id="latitude">
          <input type="hidden" wire:model="longitude" id="longitude">

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input
              type="text"
              class="form-control @if($hasError('email')) is-invalid @endif"
              id="email"
              name="email"
              wire:model.live.debounce.300ms="email"
              placeholder="{{ config('app.locale') == 'es' ? 'Ingresa tu nombre de usuario o email' : 'Enter your username or email' }}"
              autofocus />
            <label for="email">
              @if(config('app.locale') == 'es')Nombre de usuario o Email @else Username or Email @endif
            </label>
            @if($hasError('email'))
              <div class="invalid-feedback d-block">{{ $getError('email') }}</div>
            @endif
          </div>

          <div class="mb-5">
            <div class="form-password-toggle form-control-validation">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input
                    type="password"
                    id="password"
                    class="form-control @if($hasError('password')) is-invalid @endif"
                    name="password"
                    wire:model.live.debounce.300ms="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <label for="password">{{ __('auth_ui.password') }}</label>
                </div>
                <span class="input-group-text cursor-pointer" onclick="togglePassword()">
                  <i class="icon-base ri ri-eye-off-line icon-20px" id="passwordToggleIcon"></i>
                </span>
              </div>
              @if($hasError('password'))
                <div class="invalid-feedback d-block">{{ $getError('password') }}</div>
              @endif
            </div>
          </div>

          <div class="mb-5 d-flex justify-content-between mt-5">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="remember-me" wire:model="remember" />
              <label class="form-check-label" for="remember-me"> {{ __('auth_ui.remember_me') }} </label>
            </div>
            <a href="{{ route('password.request') }}" class="float-end mb-1 mt-2">
              <span>{{ __('auth_ui.forgot_password') }}</span>
            </a>
          </div>

          <div class="mb-5">
            <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
              <span wire:loading.remove>{{ __('auth_ui.login_button') }}</span>
              <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Iniciando sesión...
              </span>
            </button>
          </div>
        </form>

        <div class="divider my-5">
          <div class="divider-text">or</div>
        </div>

        <div class="d-flex justify-content-center gap-2">
          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-facebook">
            <i class="icon-base ri ri-facebook-fill icon-18px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-twitter">
            <i class="icon-base ri ri-twitter-fill icon-18px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-github">
            <i class="icon-base ri ri-github-fill icon-18px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-google-plus">
            <i class="icon-base ri ri-google-fill icon-18px"></i>
          </a>
        </div>
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>

@push('scripts')
<script>
  // Toggle password visibility
  function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordToggleIcon');

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

  // Geolocation
  document.addEventListener('livewire:initialized', () => {
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
  });
</script>
@endpush
