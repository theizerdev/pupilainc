<!DOCTYPE html>
<html
  lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  class="layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="ltr"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="{{ asset('materialize/assets/') }}/"
  data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ config('app.name', 'Laravel') }} - Mensajería</title>
    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
   <link rel="icon" type="image/x-icon" href="/logo/1719430882.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="/materialize/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/pickr/pickr-themes.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/materialize/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="/materialize/assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="/materialize/assets/vendor/libs/swiper/swiper.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="/materialize/assets/vendor/css/pages/cards-statistics.css" />

    @stack('styles')

    <!-- Helpers -->
    <script src="/materialize/assets/vendor/js/helpers.js"></script>
    <script src="/materialize/assets/vendor/js/template-customizer.js"></script>
    <script src="/materialize/assets/js/config.js"></script>

    <!-- Estilos adicionales de admin -->
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

        /* Estilos específicos para mensajería -->
        .mensajeria-styles
        /* Chat/Mensajería Styles */
        .chat-container {
            height: calc(100vh - 200px);
            display: flex;
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
        }

        .chat-sidebar {
            width: 350px;
            border-right: 1px solid #e6e5e8;
            display: flex;
            flex-direction: column;
        }

        .chat-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e6e5e8;
            background: #f8f7fa;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: #fafafa;
        }

        .chat-input {
            padding: 1.5rem;
            border-top: 1px solid #e6e5e8;
            background: #fff;
        }

        .message-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .message-item:hover {
            background-color: #f8f7fa;
        }

        .message-item.active {
            background-color: #e7e7ff;
            border-left: 3px solid #696cff;
        }

        .message-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #696cff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .message-body {
            flex: 1;
            background: #fff;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.1);
        }

        .message-body.own {
            background: #696cff;
            color: white;
        }

        .message-time {
            font-size: 0.75rem;
            color: #a5a3ae;
            margin-top: 0.5rem;
        }

        .chat-input-area {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }

        .chat-textarea {
            flex: 1;
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.75rem;
            resize: none;
            font-family: inherit;
        }

        .chat-textarea:focus {
            outline: none;
            border-color: #696cff;
            box-shadow: 0 0 0 0.125rem rgba(105, 108, 255, 0.25);
        }

        .btn-chat-send {
            background: #696cff;
            color: white;
            border: none;
            border-radius: 0.375rem;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-chat-send:hover {
            background: #5f61e6;
        }

        .unread-badge {
            background: #ff4d49;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            min-width: 20px;
            text-align: center;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .status-online {
            background: #72e128;
        }

        .status-offline {
            background: #a5a3ae;
        }

        .status-busy {
            background: #ffab00;
        }

        @media (max-width: 768px) {
            .chat-sidebar {
                width: 100%;
            }
            
            .chat-container {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.partials.menu')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('components.partials.navbar')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-fluid flex-grow-1 container-p-y" id="main-content">
                      {{ $slot }}
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('components.partials.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

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
    <script src="/materialize/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/materialize/assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="/materialize/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="/materialize/assets/vendor/libs/swiper/swiper.js"></script>

    <!-- Main JS -->
    <script src="/materialize/assets/js/main.js"></script>

    <!-- Page JS -->
    @stack('page-js')

    <!-- Accessibility improvements -->
    <script>
      // Mejorar la navegación por teclado
      document.addEventListener('DOMContentLoaded', function() {
        // Añadir skip links para usuarios de teclado
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

    <!-- Scripts para mensajería -->
    <script>
        // Auto-scroll to bottom of chat messages
        function scrollToBottom() {
            const messagesContainer = document.querySelector('.chat-messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        // Initialize chat functionality
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });

        // Scroll to bottom when new messages arrive (Livewire hook)
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.added', ({ el }) => {
                if (el.closest('.chat-messages')) {
                    scrollToBottom();
                }
            });
        });
    </script>
</body>
</html>