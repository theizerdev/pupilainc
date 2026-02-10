<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <!-- Reset Password -->
      <div class="card p-md-7 p-1">
        <!-- Logo -->
         @include('auth.header.logo')
        <!-- /Logo -->

        <div class="card-body mt-1">
          <h4 class="mb-1">{{ __('auth_ui.reset_password_title') }} 🔒</h4>
          <p class="mb-5">{{ __('auth_ui.reset_password_subtitle') }} <span class="fw-medium">{{ $email }}</span></p>

          @if (session()->has('status'))
            <div class="alert alert-success" role="alert">
              {{ session('status') }}
            </div>
          @endif

          @if ($successMessage)
            <div class="alert alert-success" role="alert">
              {{ $successMessage }}
            </div>
          @endif

          <form wire:submit="resetPassword">
            @csrf
            <input type="hidden" wire:model="token">
            <div class="form-floating form-floating-outline mb-5 form-control-validation">
              <input
                type="email"
                class="form-control @if($hasError('email')) is-invalid @endif"
                id="email"
                name="email"
                wire:model="email"
                placeholder="{{ __('auth_ui.email') }}"
                autofocus />
              <label for="email">{{ __('auth_ui.email') }}</label>
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
                      wire:model="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" />
                    <label for="password">{{ __('auth_ui.new_password') }}</label>
                    @if($hasError('password'))
                      <div class="invalid-feedback d-block">{{ $getError('password') }}</div>
                    @endif
                  </div>
                  <span class="input-group-text cursor-pointer"
                    ><i class="icon-base ri ri-eye-off-line icon-20px"></i
                  ></span>
                </div>
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
              <button class="btn btn-primary d-grid w-100" type="submit">{{ __('auth_ui.reset_button') }}</button>
            </div>
          </form>

          <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
              <i class="icon-base ri ri-arrow-left-s-line"></i>
              {{ __('auth_ui.back_to_login') }}
            </a>
          </div>
        </div>
      </div>
      <!-- /Reset Password -->

    </div>
  </div>
</div>