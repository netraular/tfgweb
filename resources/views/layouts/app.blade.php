<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TFG Raul 2024') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Web Icon -->
    <link rel="icon" href="{{ url('favicon.png') }}">
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body, html {
            height: 100%;
            margin: 0;
        }

        .wrapper {
            display: flex;
            height: 100%;
        }

        .sidebar {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 260px;
            transition: width 0.3s;
        }

        .sidebar.collapsed {
            width: 0;
            overflow: hidden;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .content.expanded {
            margin-left: 0;
        }

        .toggle-btn {
            position: fixed;
            top: 10px;
            left: 260px;
            z-index: 1000;
            
            transition: left 0.3s;
        }

        .sidebar.collapsed + .toggle-btn {
            left: 10px;
        }

        .footer-nav {
            padding-bottom: 20px;
        }

        .footer-nav .container-fluid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="app" class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar d-lg-block navbar-collapse">
            <div id="sidenav-1" class="sidenav d-flex flex-column" role="navigation">
                <a class="d-flex justify-content-center py-4" href="/">
                    <img src="{{ asset('images/netshibaLogoText.png') }}" alt="UAB Logo" height="40"/>
                </a>
                <div class="position-sticky flex-grow-1">
                    <div class="list-group list-group-flush mx-3 mt-4">
                        <a href="/" class="list-group-item list-group-item-action py-2 {{ request()->is('/') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Probar asistente</span>
                        </a>
                        <a href="/testMenu" class="list-group-item list-group-item-action py-2 {{ request()->is('testMenu') ? 'active' : '' }}">
                            <i class="fas fa-chart-area fa-fw me-3"></i><span>Tests funcionalidades</span>
                        </a>
                        <a href="/audioHistory" class="list-group-item list-group-item-action py-2 {{ request()->is('audioHistory') ? 'active' : '' }}">
                            <i class="fas fa-lock fa-fw me-3"></i><span>Historial audios</span>
                        </a>
                        <a href="/assistantHistory" class="list-group-item list-group-item-action py-2 {{ request()->is('assistantHistory') ? 'active' : '' }}">
                            <i class="fas fa-lock fa-fw me-3"></i><span>Historial consultas</span>
                        </a>
                        <a href="/comparisons" class="list-group-item list-group-item-action py-2 {{ request()->is('comparisons') ? 'active' : '' }}">
                            <i class="fas fa-lock fa-fw me-3"></i><span>Comparador consultas</span>
                        </a>
                    </div>
                </div>
                <!-- Profile and Language Options at the Bottom -->
                <div class="footer-nav mx-3 mb-4">
                    <div class="container-fluid">
                        <!-- Profile -->
                        <li class="nav-item dropdown list-unstyled me-3">
                            <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="{{ asset('images/profilePic.png') }}" height="50" alt="" />
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Mi perfil</a>
                                <a class="dropdown-item" href="#">Ajustes</a>
                                <a class="dropdown-item" href="#">Salir</a>
                            </div>
                        </li>
                        <!-- Language Options -->
                        <li class="nav-item dropdown list-unstyled">
                            <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                                @if(app()->getLocale() == 'en')
                                    <x-flag-country-gb height="20" />
                                @elseif(app()->getLocale() == 'es')
                                    <x-flag-country-es height="20" />
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('change.language', ['locale' => 'en']) }}">
                                    <x-flag-country-gb height="20" /> English{!! app()->getLocale() == 'en' ? "<i class='bi bi-check-lg' style='color:green'></i>" : "" !!}
                                </a>
                                <a class="dropdown-item" href="{{ route('change.language', ['locale' => 'es']) }}">
                                    <x-flag-country-es height="20" /> Español{!! app()->getLocale() == 'es' ? "<i class='bi bi-check-lg' style='color:green'></i>" : "" !!}
                                </a>
                            </div>
                        </li>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Sidebar -->

        <!-- Main view -->
        <main id="content" class="content">
            <div class="container mt-5 pt-5">
                @yield('content')
            </div>
        </main>
        <!-- Toggle Button -->
        <button id="toggle-btn" class="btn btn-outline-secondary toggle-btn">
            <i id="toggle-icon" class="bi bi-arrow-bar-left"></i>
        </button>
    </div>

    <script>
        document.getElementById('toggle-btn').addEventListener('click', function() {
            var sidebar = document.getElementById('sidebar');
            var content = document.getElementById('content');
            var icon = document.getElementById('toggle-icon');
            var toggleBtn = document.getElementById('toggle-btn');

            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');

            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'bi bi-arrow-bar-right';
                content.style.marginLeft = '0';
                toggleBtn.style.left = '10px';
            } else {
                icon.className = 'bi bi-arrow-bar-left';
                content.style.marginLeft = '260px'; // Ajusta el margen para que coincida con el ancho del sidebar
                toggleBtn.style.left = '260px'; // Posiciona el botón a la derecha del sidebar
            }
        });

        // Asegúrate de que el contenido esté inicialmente posicionado correctamente
        document.addEventListener('DOMContentLoaded', function() {
            var content = document.getElementById('content');
            var toggleBtn = document.getElementById('toggle-btn');
            if (!document.getElementById('sidebar').classList.contains('collapsed')) {
                content.style.marginLeft = '260px'; // Ajusta el margen para que coincida con el ancho del sidebar
                toggleBtn.style.left = '260px'; // Posiciona el botón a la derecha del sidebar
            }
        });
    </script>
</body>
</html>
