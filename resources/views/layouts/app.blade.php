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






     
  <!-- Sidebar -->
  <nav id="navbarSupportedContent" class="collapse d-lg-block sidebar navbar-collapse" >
    <div id="sidenav-1" class="sidenav" role="navigation">
      <a class=" d-flex justify-content-center py-4" href="/">
        <img src="{{ asset('images/netshibaLogoText.png') }}" alt="UAB Logo" height="40"/>
      </a>
      <div class="position-sticky">
        <div class="list-group list-group-flush mx-3 mt-4 ">
          <a href="/" class="list-group-item list-group-item-action py-2 {{ request()->is('/') ? 'active' : '' }}" >
            <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Principal</span>
          </a>
          <a href="/testMenu" class="list-group-item list-group-item-action py-2 {{ request()->is('testMenu') ? 'active' : '' }}" >
            <i class="fas fa-chart-area fa-fw me-3"></i><span>Zona test</span>
          </a>
          <a href="#" class="list-group-item list-group-item-action py-2 {{ request()->is('history') ? 'active' : '' }}">
            <i class="fas fa-lock fa-fw me-3"></i><span>Historial</span>
          </a>
        </div>
      </div>
    </div>
  </nav>
  <!-- Sidebar -->

</head>
<body>
    <div id="app" >
      <!--Main view-->
      <main role="main">
        
      <!-- Navbar -->
        <nav class="ml-5 navbar navbar-expand navbar-light fixed" >
          <div class="container-fluid d-flex justify-content-between">
            
            <!-- Sidebar Toggler -->
            <button class="btn d-block d-lg-none navbar-toggler me-3" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-2 "></i>
            </button>

            <!-- Chatbot form -->
            <div class="navbar-brand flex-fill text-center">
                <div class="input-group">
                    <button class="btn btn-outline-secondary" type="button"><i class="bi bi-mic fs-4"></i></button>
                    <input type="text" class="form-control" placeholder="¿Qué cosas puedes hacer?">
                    <button class="btn btn-outline-secondary" type="button"><i class="bi bi-arrow-up-square-fill fs-4"></i></button>
                </div>
            </div>

            <!-- Right Side Of Navbar -->
            
            <ul class="navbar-nav d-flex flex-row align-items-center">
              
              <!-- Profile -->
              <li class="nav-item dropdown">
                  <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                  <img src="{{ asset('images/profilePic.png') }}" height="50" alt="" />
                  </a>
                  <div class="dropdown-menu dropdown-menu-end" >
                      <a class="dropdown-item" href="#">Mi perfil</a>
                      <a class="dropdown-item" href="#">Ajustes</a>
                      <a class="dropdown-item" href="#">Salir</a>
                  </div>
              </li>

              <!-- General options -->
              <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                @if(app()->getLocale() == 'en')
                    <x-flag-country-gb height="20" />
                @elseif(app()->getLocale() == 'es')
                    <x-flag-country-es height="20" />
                @endif                </a>
                <div class="dropdown-menu dropdown-menu-end" >
                  <a class="dropdown-item" href="{{ route('change.language', ['locale' => 'en']) }}">
                    <x-flag-country-gb height="20" /> English{!! app()->getLocale() == 'en' ? "<i class='bi bi-check-lg' style='color:green'></i>" : "" !!}
                  </a>
                  <a class="dropdown-item" href="{{ route('change.language', ['locale' => 'es']) }}">
                    <x-flag-country-es height="20" /> Español{!! app()->getLocale() == 'es' ? "<i class='bi bi-check-lg' style='color:green'></i>" : "" !!}
                  </a>
                </div>
              </li>

            </ul>

          </div>
        </nav>

        <div class="container mt-5 pt-5">
            @yield('content')
        </div>
      </main>

    </div>

  </body>
</html>
<script>
    // Add event listener to the sidebar toggle button
    document.querySelector('.navbar-toggler').addEventListener('click', function() {
        // Toggle the CSS class on the top navbar when the sidebar is toggled
        document.querySelector('.navbar').classList.toggle('navbar-expanded');
        // Change the icon based on the visibility of the sidebar
        var icon = document.querySelector('.navbar-toggler i');
        if (document.querySelector('.navbar').classList.contains('navbar-expanded')) {
            icon.className = 'bi bi-arrow-bar-left fs-2';
        } else {
            icon.className = 'bi bi-list fs-2';
        }
    });
</script>

