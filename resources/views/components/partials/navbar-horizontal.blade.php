<nav
  class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
  <div class="container-xxl">
    <!-- Logo y marca -->
    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
      <a href="{{ route('admin.dashboard') }}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">
         <img src="/logo/auth.png" height="40px">
        </span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
        <i class="icon-base ri ri-close-line icon-sm"></i>
      </a>
    </div>

    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base ri ri-menu-line icon-22px"></i>
      </a>
    </div>

  <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
    <!-- Search
    <div class="navbar-nav align-items-center me-sm-2 me-xl-0">
      <div class="nav-item navbar-search-wrapper me-sm-2 me-xl-0">
        <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
          <span class="d-inline-block text-body-secondary fw-normal" id="autocomplete"></span>
        </a>
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-md-auto">
      <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <i class="icon-base ri ri-translate-2 icon-md"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item {{ app()->getLocale() === 'es' ? 'active' : '' }}" href="{{ route('lang.switch', 'es') }}">
              <span>🇪🇸 Español</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('lang.switch', 'en') }}">
              <span>🇺🇸 English</span>
            </a>
          </li>
        </ul>
      </li>
      <!--/ Language -->

      <!-- Style Switcher -->
      <li class="nav-item dropdown me-sm-2 me-xl-0">
        <a
          class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
          id="nav-theme"
          href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <i class="icon-base ri ri-sun-line icon-22px theme-icon-active"></i>
          <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
          <li>
            <button
              type="button"
              class="dropdown-item align-items-center active"
              data-bs-theme-value="light"
              aria-pressed="false">
              <span><i class="icon-base ri ri-sun-line icon-22px me-3" data-icon="sun-line"></i>Light</span>
            </button>
          </li>
          <li>
            <button
              type="button"
              class="dropdown-item align-items-center"
              data-bs-theme-value="dark"
              aria-pressed="true">
              <span
                ><i class="icon-base ri ri-moon-clear-line icon-22px me-3" data-icon="moon-clear-line"></i
                >Dark</span
              >
            </button>
          </li>
          <li>
            <button
              type="button"
              class="dropdown-item align-items-center"
              data-bs-theme-value="system"
              aria-pressed="false">
              <span
                ><i class="icon-base ri ri-computer-line icon-22px me-3" data-icon="computer-line"></i
                >System</span
              >
            </button>
          </li>
        </ul>
      </li>
      <!-- / Style Switcher-->

      <!-- Quick links -->
      <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-sm-2 me-xl-0">
        <a
          class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
          href="javascript:void(0);"
          data-bs-toggle="dropdown"
          data-bs-auto-close="outside"
          aria-expanded="false">
          <i class="icon-base ri ri-star-smile-line icon-22px"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0">
          <div class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="mb-0 me-auto">Accesos rápidos</h6>
              <a
                href="javascript:void(0)"
                class="btn btn-text-secondary rounded-pill btn-icon dropdown-shortcuts-add text-heading"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Add shortcuts">
                <i class="icon-base ri ri-add-line text-heading"></i>
              </a>
            </div>
          </div>
          <div class="dropdown-shortcuts-list scrollable-container">
            <div class="row row-bordered overflow-visible g-0">
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="icon-base ri ri-calendar-line icon-26px text-heading"></i>
                </span>
                <a href="{{ route('admin.dashboard') }}" class="stretched-link">Dashboard</a>
                <small>Inicio</small>
              </div>
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="icon-base ri ri-user-line icon-26px text-heading"></i>
                </span>
                <a href="{{ route('admin.users.profile') }}" class="stretched-link">Perfil</a>
                <small>Mi cuenta</small>
              </div>
            </div>
            <div class="row row-bordered overflow-visible g-0">
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="icon-base ri ri-settings-4-line icon-26px text-heading"></i>
                </span>
                <a href="{{ route('admin.users.profile') }}" class="stretched-link">Configuración</a>
                <small>Preferencias</small>
              </div>
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="icon-base ri ri-question-line icon-26px text-heading"></i>
                </span>
                          <a href="javascript:void(0);" class="stretched-link">Ayuda</a>
                          <small>Soporte</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <!--/ Quick links -->

                <!-- Notification -->
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-4 me-xl-1">
                  @livewire('notification-bell')
                </li>
                <!--/ Notification -->

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      @if(Auth::check() && Auth::user()->initials)
                        <span class="avatar-initials bg-primary text-white">{{ Auth::user()->initials }}</span>
                      @else
                        <img src="{{ asset('materialize/assets/img/avatars/1.png') }}" alt="avatar" class="rounded-circle" />
                      @endif
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
                    <li>
                      <a class="dropdown-item" href="{{ route('admin.users.profile') }}">
                        <div class="d-flex align-items-center">
                          <div class="flex-shrink-0 me-2">
                            <div class="avatar avatar-online">
                              @if(Auth::check() && Auth::user()->initials)
                                <span class="avatar-initials bg-primary text-white">{{ Auth::user()->initials }}</span>
                              @else
                                <img src="{{ asset('materialize/assets/img/avatars/1.png') }}" alt="avatar" class="w-px-40 h-auto rounded-circle" />
                              @endif
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-0 small">{{ Auth::check() && Auth::user() ? Auth::user()->name : 'Usuario' }}</h6>
                            <small class="text-body-secondary">{{ Auth::check() && Auth::user() ? Auth::user()->email : '' }}</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('admin.users.profile') }}">
                        <i class="icon-base ri ri-user-3-line icon-22px me-3"></i
                        ><span class="align-middle">Mi perfil</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>

                    <li>
                        <div class="d-grid px-4 pt-2 pb-1">
                      <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                        <span>Salir del sistema</span>
                       </a>
                       <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                         @csrf
                      </form>
                     </div>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </div>
        </nav>
 <style>
        /* Estilos para avatar con iniciales */
    .avatar-initials {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        font-weight: 500;
        font-size: 1rem;
        background-color: #007bff;
        color: white;
    }

    /* Versión para avatares con fondo label */
    .avatar-initials.bg-label-primary {
        background-color: #e0f1ff;
        color: #007bff;
    }

    /* Asegurar que los avatares tengan el tamaño correcto */
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border-radius: 50%;
        text-align: center;
        vertical-align: middle;
    }
</style>
