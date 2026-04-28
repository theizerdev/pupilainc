<!DOCTYPE html>
<html
  lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  class="layout-navbar-fixed layout-menu-fixed{{ isset($templateSettings) && $templateSettings->menu_collapsed ? ' layout-menu-collapsed' : '' }}{{ isset($templateSettings) ? ($templateSettings->content_layout === 'wide' ? ' layout-wide' : ' layout-compact') : ' layout-compact' }}{{ isset($templateSettings) && $templateSettings->footer_fixed ? ' layout-footer-fixed' : '' }}"
  dir="{{ isset($templateSettings) ? $templateSettings->text_direction : 'ltr' }}"
  data-skin="{{ isset($templateSettings) ? ($templateSettings->skin == 1 ? 'bordered' : 'default') : 'default' }}"
  data-bs-theme="{{ isset($templateSettings) ? $templateSettings->theme : 'light' }}"
  data-assets-path="{{ asset('materialize/assets/') }}/"
  data-template="{{ isset($templateSettings) && $templateSettings->layout_type === 'horizontal' ? 'horizontal-menu-template' : 'vertical-menu-template' }}">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ config('app.name') }} - {{ config('app.description') }}" />

    <!-- Favicon -->
      <link rel="icon" type="image/x-icon" href="/logo/1719430882.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="/materialize/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="/materialize/assets/vendor/libs/node-waves/node-waves.css" />

    <script src="/materialize/assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <link rel="stylesheet" href="/materialize/assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="/materialize/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/materialize/assets/css/demo.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="/materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->

    <link rel="stylesheet" href="/materialize/assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/swiper/swiper.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="/materialize/assets/vendor/css/pages/cards-statistics.css" />

    <!-- Helpers -->
    <script src="/materialize/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js. -->
    <script src="/materialize/assets/vendor/js/template-customizer.js"></script>

    <!--? Config: Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. -->

    <script src="/materialize/assets/js/config.js"></script>

    @include('components.template-config')

     <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

    <!-- Chat y Biblioteca CSS -->
    <link rel="stylesheet" href="{{ asset('css/chat-biblioteca.css') }}">

    @if(isset($templateSettings) && $templateSettings->primary_color)
    <style>
      :root {
        --bs-primary: {{ $templateSettings->primary_color }};
        --bs-primary-rgb: {{ implode(',', sscanf($templateSettings->primary_color, '#%02x%02x%02x')) }};
      }
    </style>
    @endif

  <style>
    .photo-preview-card {
      transition: transform 0.2s;
    }

    .photo-preview-card:hover {
      transform: scale(1.02);
    }

    .camera-feed {
      background: #000;
      border-radius: 0.5rem;
    }

    .capture-btn {
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Estilos para breadcrumbs */
    .breadcrumb-style {
      background-color: transparent;
      padding: 0.75rem 1rem;
      margin-bottom: 1rem;
      border-radius: 0.375rem;
      font-size: 0.875rem;
    }

    .breadcrumb-style .breadcrumb-item + .breadcrumb-item::before {
      content: ">";
      color: #6c757d;
    }

    .breadcrumb-style .breadcrumb-item a {
      color: #007bff;
      text-decoration: none;
    }

    .breadcrumb-style .breadcrumb-item a:hover {
      text-decoration: underline;
    }

    .breadcrumb-style .breadcrumb-item.active {
      color: #495057;
    }

    /* Mejoras de accesibilidad */
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border-width: 0;
    }

    /* Estilos para notificaciones */
    .notification-badge {
      position: absolute;
      top: 0;
      right: 0;
      transform: translate(50%, -50%);
      padding: 0.25em 0.5em;
      font-size: 0.75em;
      border-radius: 50%;
      min-width: 1.5em;
      min-height: 1.5em;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
      .breadcrumb-style {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
      }

      .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
      }
    }
  </style>
    @livewireStyles
    @stack('styles')
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        @include('components.partials.menu')

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('components.partials.navbar')

          <!-- Content wrapper -->
          <div class="content-wrapper">

            <!-- Content -->
            <div class="container-fluid flex-grow-1 container-p-y">

              <!-- Componente de notificaciones global -->
              <x-notifications />

              @yield('content')
            </div>
            <!-- /Content -->

            <!-- Footer -->
            @include('components.partials.footer')

            <div class="content-backdrop fade"></div>
          </div>
          <!-- /Content wrapper -->
        </div>
        <!-- /Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- /Layout wrapper -->

    <!-- Toast Container -->
    @include('components.toast-container')

    <!-- Core JS -->
     <script src="/materialize/assets/vendor/libs/jquery/jquery.js"></script>

    <script src="/materialize/assets/vendor/libs/popper/popper.js"></script>
    <script src="/materialize/assets/vendor/js/bootstrap.js"></script>
    <script src="/materialize/assets/vendor/libs/node-waves/node-waves.js"></script>

    <script src="/materialize/assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <script src="/materialize/assets/vendor/libs/pickr/pickr.js"></script>

    <script src="/materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="/materialize/assets/vendor/libs/hammer/hammer.js"></script>

    <script src="/materialize/assets/vendor/libs/i18n/i18n.js"></script>

    <script src="/materialize/assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="/materialize/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="/materialize/assets/vendor/libs/swiper/swiper.js"></script>

    <!-- Main JS -->

    <script src="/materialize/assets/js/main.js"></script>


   <!-- Cropper.js JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @stack('scripts')
    @livewireScripts
    <!-- Accessibility improvements -->
    <script>
      // Mejorar la navegaciÃƒÂ³n por teclado
      document.addEventListener('DOMContentLoaded', function() {
        // AÃƒÂ±adir skip links para usuarios de teclado
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'sr-only sr-only-focusable';
        skipLink.textContent = 'Saltar al contenido principal';
        skipLink.style.cssText = `
          position: absolute;
          top: 10px;
          left: 10px;
          background: #000;
          color: #fff;
          padding: 10px;
          z-index: 10000;
        `;

        skipLink.addEventListener('focus', function() {
          this.style.top = '10px';
        });

        skipLink.addEventListener('blur', function() {
          this.style.top = '-40px';
        });

        document.body.insertBefore(skipLink, document.body.firstChild);

        // Asegurar que todos los elementos interactivos tengan focus visible
        const focusableElements = document.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
        focusableElements.forEach(element => {
          if (!element.hasAttribute('aria-label') && !element.hasAttribute('aria-labelledby') && element.title) {
            element.setAttribute('aria-label', element.title);
          }
        });
      });
    </script>

    <!-- Sistema de Notificaciones Global -->
    <x-notifications />

  </body>
</html>
