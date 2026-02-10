<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
    <div class="authentication-inner py-6">
      <!-- Verify Email -->
      <div class="card p-md-7 p-1">
        <!-- Logo -->
         @include('auth.header.logo')
        <!-- /Logo -->

        <div class="card-body mt-1">
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
              <button class="btn btn-primary d-grid w-100" type="submit">Click here to resend</button>

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
        </div>
      </div>
      <!-- /Verify Email -->

    </div>
  </div>
</div>
