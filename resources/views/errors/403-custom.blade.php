<!doctype html>

<html
  lang="es"
  class="layout-wide customizer-hide"
  dir="ltr"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="{{ asset('materialize/assets/') }}/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Acceso Denegado - Medical System</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('materialize/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('materialize/assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('materialize/assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('materialize/assets/vendor/libs/pickr/pickr-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('materialize/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('materialize/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('materialize/assets/vendor/css/pages/page-misc.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('materialize/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('materialize/assets/js/config.js') }}"></script>

    <style>
        .module-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid rgba(0,0,0,0.05);
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            height: 100%;
        }
        .module-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .module-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .module-info {
            flex-grow: 1;
        }
        .module-title {
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 0.2rem;
        }
        .module-desc {
            font-size: 0.75rem;
            color: #a1acb8;
        }
    </style>
  </head>

  <body>
    <!-- Content -->

    <!-- Error -->
    <div class="misc-wrapper">
      <h1 class="mb-2 mx-2" style="font-size: 6rem; line-height: 6rem">403</h1>
      <h4 class="mb-2">¡No autorizado! 🛑</h4>
      <p class="mb-2 mx-2">
        {{ $message ?? 'No tienes permisos para acceder a esta sección.' }}
      </p>
      
      @if(isset($required_permission))
      <div class="alert alert-danger d-inline-block p-2 mb-3">
          <small><i class="fas fa-lock me-1"></i> Permiso requerido: <strong>{{ $required_permission }}</strong></small>
      </div>
      @endif

      <div class="d-flex justify-content-center mt-9">
        <img
          src="{{ asset('materialize/assets/img/illustrations/misc-not-authorized-object.png') }}"
          alt="misc-not-authorized"
          class="img-fluid misc-object d-none d-lg-inline-block"
          width="160" />
        <img
          src="{{ asset('materialize/assets/img/illustrations/misc-bg-light.png') }}"
          alt="misc-error"
          class="misc-bg d-none d-lg-inline-block"
          data-app-light-img="illustrations/misc-bg-light.png"
          data-app-dark-img="illustrations/misc-bg-dark.png" />
        <div class="d-flex flex-column align-items-center">
          <img
            src="{{ asset('materialize/assets/img/illustrations/misc-not-authorized-illustration.png') }}"
            alt="misc-not-authorized"
            class="img-fluid z-1"
            width="190" />
          
          <div class="mt-4 text-center">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mb-3">Volver al Inicio</a>
            
            @if(isset($alternative_modules) && count($alternative_modules) > 0)
                <div class="mt-4 text-start" style="max-width: 600px;">
                    <h6 class="text-center mb-3 text-muted">Módulos disponibles para ti:</h6>
                    <div class="row g-3">
                        @foreach($alternative_modules as $module)
                            <div class="col-md-6 col-12">
                                <div class="module-card" onclick="window.location.href='{{ $module['route'] }}'">
                                    <div class="module-icon" style="background: {{ $module['color'] }};">
                                        <i class="{{ $module['icon'] }}"></i>
                                    </div>
                                    <div class="module-info">
                                        <div class="module-title">{{ $module['title'] }}</div>
                                        <div class="module-desc">{{ $module['description'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
          </div>
        </div>
      </div>
    </div>
    <!-- /Error -->

    <!-- / Content -->

    <!-- Core JS -->
    <script src="{{ asset('materialize/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('materialize/assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('materialize/assets/js/main.js') }}"></script>
  </body>
</html>