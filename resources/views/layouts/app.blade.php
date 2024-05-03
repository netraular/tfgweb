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
  <nav class="collapse d-lg-block sidebar collapse h-full pb-[150px] md:block" >
    <div id="sidenav-1" class="sidenav" role="navigation">
      <a class=" d-flex justify-content-center py-4" href="#">
        <img src="{{ asset('images/netshibaLogoText.png') }}" alt="UAB Logo" height="40"/>
      </a>
      <div class="position-sticky">
        <div class="list-group list-group-flush mx-3 mt-4 ">
          <a href="#" class="list-group-item list-group-item-action py-2 active" >
            <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Principal</span>
          </a>
          <a href="#" class="list-group-item list-group-item-action py-2 " >
            <i class="fas fa-chart-area fa-fw me-3"></i><span>Zona test</span>
          </a>
          <a href="#" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fas fa-lock fa-fw me-3"></i><span>Historial</span>
          </a>
        </div>
      </div>
    </div>
  </nav>
  <!-- Sidebar -->

  <!-- Navbar -->
</head>
<body>
    <div id="app" >
      <!--Main view-->
      <main role="main">
        
        <nav class="ml-5 navbar navbar-expand-lg navbar-light fixed" >
          <!-- Container wrapper -->
          <div class="container-fluid ">

            <!-- Left Side Of Navbar -->
            <!-- Toggler -->
            <button class="btn d-block d-lg-none">
              <i class="bi bi-list fs-2"></i>
            </button>
            <!-- Search form -->
            <div class="input-group mb-3">
              <button class="btn btn-outline-secondary" type="button"><i class="bi bi-mic fs-4"></i></button>
              <input type="text" class="form-control" placeholder="¿Que cosas puedes hacer?" >
              <button class="btn btn-outline-secondary" type="button"><i class="bi bi-arrow-up-square-fill fs-4"></i></button>
            </div>
                
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav">
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
                  <i class="bi bi-three-dots-vertical fs-2"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end" >
                  <a class="dropdown-item" href="#">
                    <x-flag-country-gb height="20" /> English<i class="bi bi-check-lg" style="color:green"></i>
                  </a>
                  <a class="dropdown-item" href="#">
                    <x-flag-country-es height="20" /> Español<i class="bi bi-check"></i>
                  </a>
                </div>
              </li>

            </ul>

          </div>
        </nav>

        <div class="container mt-5">
            @yield('content')
        </div>
      </main>

    </div>

  </body>
</html>

