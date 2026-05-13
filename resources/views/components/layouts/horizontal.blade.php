<!DOCTYPE html>
<html
  lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  class="layout-menu-fixed layout-navbar-fixed {{ isset($templateSettings) ? ($templateSettings->content_layout === 'wide' ? 'layout-wide' : 'layout-compact') : 'layout-compact' }}{{ isset($templateSettings) && $templateSettings->footer_fixed ? ' layout-footer-fixed' : '' }}"
  dir="{{ isset($templateSettings) ? $templateSettings->text_direction : 'ltr' }}"
  data-skin="{{ isset($templateSettings) ? ($templateSettings->skin == 1 ? 'bordered' : 'default') : 'default' }}"
  data-bs-theme="{{ isset($templateSettings) ? $templateSettings->theme : 'light' }}"
  data-assets-path="{{ asset('materialize/assets/') }}/"
  data-template="horizontal-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ 'PUPILA INC' }}</title>
    <meta name="description" content="{{ config('app.name') }} - {{ config('app.description') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
      <link rel="icon" type="image/x-icon" href="/logo/favicon-32x32.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="/materialize/assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/node-waves/node-waves.css" />
    <script src="/materialize/assets/vendor/libs/@algolia/autocomplete-js.js"></script>
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/pickr/pickr-themes.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/materialize/assets/css/demo.css" />

    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/swiper/swiper.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/sweetalert2/sweetalert2.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="/materialize/assets/vendor/css/pages/cards-statistics.css" />

    <!-- Helpers -->
    <script src="/materialize/assets/vendor/js/helpers.js"></script>
    <script src="/materialize/assets/vendor/js/template-customizer.js"></script>
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
      .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
        color: white;
      }
    </style>

    @stack('styles')
    @livewireStyles
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
      <div class="layout-container">

        <!-- /Navbar -->
      @include('components.partials.navbar-horizontal')

        <!-- Layout page -->
        <div class="layout-page">
          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu flex-grow-0">
              <div class="container-fluid d-flex h-100">
                @include('components.partials.horizontal-menu')
              </div>
            </aside>

            <!-- Content -->
            <div class="container-fluid flex-grow-1 container-p-y">

              <!-- Componente de notificaciones global -->


              {{ $slot }}
            </div>
            <!-- /Content -->

            <!-- Footer -->
            @include('components.partials.footer-horizontal')

            <div class="content-backdrop fade"></div>
          </div>
          <!-- /Content wrapper -->
        </div>
        <!-- /Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- /Layout wrapper -->
     @livewireScripts
    @livewire('chat-notifications')
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

    <!-- Vendors JS -->
    <script src="/materialize/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="/materialize/assets/vendor/libs/swiper/swiper.js"></script>
     <script src="/materialize/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

    <!-- Main JS -->
    <script src="/materialize/assets/js/main.js"></script>

    <!-- Cropper.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Toast Container -->

    @include('components.toast-container')

    <!-- Widget de Chat Flotante Global -->
    @livewire('floating-chat-widget')

    <!-- Timer Global de Dilatación -->
    @livewire('dilatation-timer')

    <!-- Script global para manejar eventos de notificaciones -->

     <!-- Configuración de Livewire para manejar CSRF automáticamente -->
    <script>
        // Configurar Livewire para auto-refresh cuando el token CSRF expira
        // en lugar de mostrar el modal de "Page Expired"
        window.addEventListener('DOMContentLoaded', function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.on('csrf-token-mismatch', function() {
                    // Auto-refresh silencioso sin mostrar modal
                    window.location.reload();
                });
            }
        });
    </script>

    @stack('scripts')
  </body>
</html>
