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

        <!-- Verify Email Code -->
        <h4 class="mb-1">{{ __('auth_ui.verify_email_title') }} @if(Auth::user()->phone && Auth::user()->whatsapp_verification_enabled) 📱 @else ✉️ @endif</h4>
        <p class="text-start mb-5">
          @if(Auth::user()->phone && Auth::user()->whatsapp_verification_enabled)
            Se enviará un código de verificación a tu WhatsApp al número <span class="fw-medium">{{ Auth::user()->phone }}</span>.
          @else
            {{ __('auth_ui.verify_email_subtitle') }} <span class="fw-medium">{{ Auth::user()->email }}</span>.
          @endif
        </p>

        @if (session('resent'))
          <div class="alert alert-success" role="alert">
            {{ session('resent') }}
          </div>
        @endif

        <form wire:submit="verifyCode">
          <div class="mb-5 form-control-validation">
            <label class="form-label">{{ __('auth_ui.verification_code') }}</label>
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
              @for ($i = 0; $i < 6; $i++)
                <input
                  type="text"
                  class="form-control text-center @if($hasError('code')) is-invalid @endif"
                  maxlength="1"
                  wire:model.live.debounce.300ms="codeInputs.{{ $i }}"
                  wire:key="code-input-{{ $i }}"
                  style="flex: 1; min-width: 40px; max-width: 50px; height: 3rem; font-size: 1.5rem; text-transform: uppercase;"
                  x-data
                  x-init="
                    $watch('$wire.codeInputs.{{ $i }}', value => {
                      if (value.length === 1 && {{ $i }} < 5) {
                        $nextTick(() => {
                          $el.nextElementSibling && $el.nextElementSibling.focus();
                        });
                      }
                    });
                  "
                />
              @endfor
            </div>
            @if($hasError('code'))
              <div class="invalid-feedback d-block">{{ $getError('code') }}</div>
            @endif
          </div>

          <div class="mb-5">
            <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
              <span wire:loading.remove>{{ __('auth_ui.verify_button') }}</span>
              <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2"></span>
                Verificando...
              </span>
            </button>
          </div>
        </form>

        <form wire:submit="sendCode">
          <div class="mb-5">
            @if($canResend)
              <button class="btn btn-outline-primary d-grid w-100" type="submit">
                {{ __('auth_ui.resend_code') }}
              </button>
            @else
              <button class="btn btn-outline-secondary d-grid w-100" type="button" disabled>
                {{ __('auth_ui.resend_code') }} ({{ floor($resendCountdown/60) }}:{{ str_pad($resendCountdown%60, 2, '0', STR_PAD_LEFT) }})
              </button>
            @endif
          </div>
        </form>

        <div class="text-start">
          <a href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
             class="d-flex align-items-center justify-content-center">
            <i class="icon-base ri ri-arrow-left-s-line"></i>
            {{ __('auth_ui.logout') }}
          </a>

          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        </div>
        <!-- /Verify Email Code -->

      </div>
    </div>
    <!-- /Right Section -->
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('livewire:load', function () {
    Livewire.on('focus-next', index => {
      const nextInput = document.querySelector(`[wire\\:model="codeInputs.${index}"]`);
      if (nextInput) {
        nextInput.focus();
      }
    });
  });
</script>
@endpush
