<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <!-- Verify Email Code -->
      <div class="card p-md-7 p-1">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{ url('/') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
              <span class="text-primary">
                <svg width="32" height="18" viewBox="0 0 38 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M30.0944 2.22569C29.0511 0.444187 26.7508 -0.172113 24.9566 0.849138C23.1623 1.87039 22.5536 4.14247 23.5969 5.92397L30.5368 17.7743C31.5801 19.5558 33.957 20.1721 35.7512 19.1509C37.4689 18.1296 38.0776 15.8575 37.0343 14.076L30.0944 2.22569Z"
                    fill="currentColor" />
                  <path
                    d="M30.171 2.22569C29.1277 0.444187 26.8274 -0.172113 25.0332 0.849138C23.2389 1.87039 22.6302 4.14247 23.6735 5.92397L30.6134 17.7743C31.6567 19.5558 33.957 20.1721 35.7512 19.1509C37.5455 18.1296 38.1542 15.8575 37.1109 14.076L30.171 2.22569Z"
                    fill="url(#paint0_linear_2989_100980)"
                    fill-opacity="0.4" />
                  <path
                    d="M22.9676 2.22569C24.0109 0.444187 26.3112 -0.172113 28.1054 0.849138C29.8996 1.87039 30.5084 4.14247 29.4651 5.92397L22.5251 17.7743C21.4818 19.5558 19.1816 20.1721 17.3873 19.1509C15.5931 18.1296 14.9843 15.8575 16.0276 14.076L22.9676 2.22569Z"
                    fill="currentColor" />
                  <path
                    d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                    fill="currentColor" />
                  <path
                    d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                    fill="url(#paint1_linear_2989_100980)"
                    fill-opacity="0.4" />
                  <path
                    d="M7.82901 2.22569C8.87231 0.444187 11.1726 -0.172113 12.9668 0.849138C14.7611 1.87039 15.3698 4.14247 14.3265 5.92397L7.38656 17.7743C6.34325 19.5558 4.04298 20.1721 2.24875 19.1509C0.454514 18.1296 -0.154233 15.8575 0.88907 14.076L7.82901 2.22569Z"
                    fill="currentColor" />
                  <defs>
                    <linearGradient
                      id="paint0_linear_2989_100980"
                      x1="5.36642"
                       y1="0.849138"
                       x2="10.532"
                      y2="24.104"
                       gradientUnits="userSpaceOnUse">
                      <stop offset="0" stop-opacity="1" />
                      <stop offset="1" stop-opacity="0" />
                    </linearGradient>
                    <linearGradient
                      id="paint1_linear_2989_100980"
                      x1="5.19475"
                       y1="0.849139"
                       x2="10.3357"
                       y2="24.1155"
                       gradientUnits="userSpaceOnUse">
                      <stop offset="0" stop-opacity="1" />
                      <stop offset="1" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                </svg>
              </span>
            </span>
            <span class="app-brand-text demo text-heading fw-semibold">{{ config('app.name', 'Laravel') }}</span>
          </a>
        </div>
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