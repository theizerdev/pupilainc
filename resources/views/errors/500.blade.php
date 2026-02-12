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
    <title>Error del Servidor - Medical System</title>

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
  </head>

  <body>
    <!-- Content -->

    <!-- Error -->
    <div class="misc-wrapper">
      <h1 class="mb-2 mx-2" style="font-size: 6rem; line-height: 6rem">500</h1>
      <h4 class="mb-2">Error Interno del Servidor 🚨</h4>
      <p class="mb-2 mx-2">
        Algo salió mal en nuestros servidores. Por favor, intenta de nuevo más tarde.
      </p>
      
      <div class="d-flex justify-content-center mt-9">
        <img
          src="{{ asset('materialize/assets/img/illustrations/misc-error-object.png') }}"
          alt="misc-error"
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
            src="{{ asset('materialize/assets/img/illustrations/misc-server-error-illustration.png') }}"
            alt="misc-error"
            class="img-fluid z-1"
            width="190" />
          
          <div class="mt-4 text-center">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mb-3">Volver al Inicio</a>
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