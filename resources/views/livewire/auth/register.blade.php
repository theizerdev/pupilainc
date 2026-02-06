<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <!-- Register -->
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
          <h4 class="mb-1">{{ __('auth_ui.register_title') }} 🚀</h4>
          <p class="mb-5">{{ __('auth_ui.register_subtitle') }}</p>

          <form wire:submit="register">
            <div class="form-floating form-floating-outline mb-5 form-control-validation">
              <input
                type="text"
                class="form-control @if($hasError('name') || $nameAvailable === false) is-invalid @elseif($nameAvailable) is-valid @endif"
                id="name"
                name="name"
                wire:model="name"
                placeholder="{{ __('auth_ui.name') }}"
                autofocus />
              <label for="name">{{ __('auth_ui.name') }}</label>
              @if($hasError('name') || $nameAvailable === false)
                <div class="invalid-feedback d-block">{{ $getError('name') ?: 'This username is already taken.' }}</div>
              @elseif($nameAvailable && strlen($name) >= 3)
                <div class="valid-feedback d-block">This username is available!</div>
              @endif
            </div>
            <div class="form-floating form-floating-outline mb-5 form-control-validation">
              <input
                type="text"
                class="form-control @if($hasError('email') || $emailAvailable === false) is-invalid @elseif($emailAvailable) is-valid @endif"
                id="email"
                name="email"
                wire:model="email"
                placeholder="{{ __('auth_ui.email') }}" />
              <label for="email">{{ __('auth_ui.email') }}</label>
              @if($hasError('email') || $emailAvailable === false)
                <div class="invalid-feedback d-block">{{ $getError('email') ?: 'This email is already registered.' }}</div>
              @elseif($emailAvailable && filter_var($email, FILTER_VALIDATE_EMAIL))
                <div class="valid-feedback d-block">This email is available!</div>
              @endif
            </div>
            <div class="form-floating form-floating-outline mb-5 form-control-validation">
              <input
                type="tel"
                class="form-control @if($hasError('phone')) is-invalid @endif"
                id="phone"
                name="phone"
                wire:model="phone"
                placeholder="{{ __('Teléfono') }}" />
              <label for="phone">{{ __('Teléfono (opcional)') }}</label>
              @if($hasError('phone'))
                <div class="invalid-feedback d-block">{{ $getError('phone') }}</div>
              @endif
              <div class="form-text">Ingresa tu número de WhatsApp para recibir códigos de verificación</div>
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
                      wire:model="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" />
                    <label for="password">{{ __('auth_ui.password') }}</label>
                    @if($hasError('password'))
                      <div class="invalid-feedback d-block">{{ $getError('password') }}</div>
                    @endif
                  </div>
                  <span class="input-group-text cursor-pointer"
                    ><i class="icon-base ri ri-eye-off-line icon-20px"></i
                  ></span>
                </div>

                <!-- Indicador de fortaleza de contraseña -->
                @if(strlen($password) > 0)
                  <div class="mt-2">
                    <div class="progress">
                      <div class="progress-bar"
                           role="progressbar"
                           style="width: {{ $passwordStrength }}%"
                           aria-valuenow="{{ $passwordStrength }}"
                           aria-valuemin="0"
                           aria-valuemax="100">
                      </div>
                    </div>
                    <div class="text-muted small mt-1">{{ $passwordFeedback }}</div>
                  </div>
                @endif
              </div>
            </div>
            <div class="mb-5">
              <div class="form-password-toggle form-control-validation">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input
                      type="password"
                      id="password_confirmation"
                      class="form-control @if($hasError('password_confirmation')) is-invalid @endif"
                      name="password_confirmation"
                      wire:model="password_confirmation"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" />
                    <label for="password_confirmation">{{ __('auth_ui.confirm_password') }}</label>
                    @if($hasError('password_confirmation'))
                      <div class="invalid-feedback d-block">{{ $getError('password_confirmation') }}</div>
                    @endif
                  </div>
                  <span class="input-group-text cursor-pointer"
                    ><i class="icon-base ri ri-eye-off-line icon-20px"></i
                  ></span>
                </div>
              </div>
            </div>
            <div class="mb-5">
              <div class="form-check mt-2">
                <input class="form-check-input @if($hasError('terms')) is-invalid @endif" type="checkbox" id="terms-conditions" name="terms" wire:model="terms" />
                <label class="form-check-label @if($hasError('terms')) is-invalid @endif" for="terms-conditions">
                  I agree to
                  <a href="javascript:void(0);">privacy policy & terms</a>
                </label>
                @if($hasError('terms'))
                  <div class="invalid-feedback d-block">{{ $getError('terms') }}</div>
                @endif
              </div>
            </div>
            <div class="mb-5">
              <button class="btn btn-primary d-grid w-100" type="submit">{{ __('auth_ui.register_button') }}</button>
            </div>
          </form>

          <p class="text-center mb-5">
            <span>{{ __('auth_ui.already_have_account') }}</span>
            <a href="{{ route('login') }}">
              <span>{{ __('auth_ui.login_here') }}</span>
            </a>
          </p>

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

            <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
              <i class="icon-base ri ri-google-fill icon-18px"></i>
            </a>
          </div>
        </div>
      </div>
      <!-- /Register -->

    </div>
  </div>
</div>