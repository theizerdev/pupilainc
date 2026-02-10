<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <!-- Verify Email Code -->
      <div class="card p-md-7 p-1">
        <!-- Logo -->
         @include('auth.header.logo')
        <!-- /Logo -->

        <div class="card-body mt-1">
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
                    wire:model="codeInputs.{{ $i }}"
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
              <button class="btn btn-primary d-grid w-100" type="submit">{{ __('auth_ui.verify_button') }}</button>
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
        </div>
      </div>
      <!-- /Verify Email Code -->

    </div>
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