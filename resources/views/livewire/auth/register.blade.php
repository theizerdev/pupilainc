<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <!-- Register -->
      <div class="card p-md-7 p-1">
        <!-- Logo -->
         @include('auth.header.logo')
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

        </div>
      </div>
      <!-- /Register -->

    </div>
  </div>
</div>