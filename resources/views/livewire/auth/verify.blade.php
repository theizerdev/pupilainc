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

        <!-- Verify Email -->
        <h4 class="mb-1">Verify your email ✉️</h4>
        <p class="text-start mb-5">
          Account activation link sent to your email address: <span class="fw-medium">{{ Auth::user()->email }}</span> Please follow the link inside to continue.
        </p>

        @if (session('resent'))
          <div class="alert alert-success" role="alert">
            {{ session('resent') }}
          </div>
        @endif

        <form wire:submit="resend">
          <div class="mb-5">
            <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
              <span wire:loading.remove>Click here to resend</span>
              <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2"></span>
                Sending...
              </span>
            </button>
          </div>
        </form>

        <div class="text-start">
          <a href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
             class="d-flex align-items-center justify-content-center">
            <i class="icon-base ri ri-arrow-left-s-line"></i>
            Log out
          </a>

          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        </div>
        <!-- /Verify Email -->

      </div>
    </div>
    <!-- /Right Section -->
  </div>
</div>
