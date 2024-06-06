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
          <a href="/audioHistory" class="list-group-item list-group-item-action py-2 {{ request()->is('history') ? 'active' : '' }}">
            <i class="fas fa-lock fa-fw me-3"></i><span>Historial voz</span>
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
                  <div>
                    <button id="record-btn-main" class="btn btn-outline-secondary" type="button" onclick="startRecordingMain()"><i class="bi bi-mic fs-4" ></i></button>
                    <!-- Loading Spinner de Bootstrap -->
                    <div id="loading-spinner2" class="spinner-border" role="status" style="display: none;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>

                    <!-- Botón para grabar audio -->
                    <button id="upload_audio" hidden onclick="uploadAudio()">Subir Audio</button>



                    <script>
                      // Variables para la grabación de audio
                      var mediaRecorder;
                      var audioChunks = [];

                      // Función para iniciar la grabación
                      function startRecordingMain() {
                          navigator.mediaDevices.getUserMedia({ audio: true })
                              .then(stream => {
                                  mediaRecorder = new MediaRecorder(stream);
                                  mediaRecorder.start();

                                  mediaRecorder.addEventListener('dataavailable', event => {
                                      audioChunks.push(event.data);
                                  });

                                  mediaRecorder.addEventListener('stop', () => {
                                      var audioBlob = new Blob(audioChunks);
                                      uploadRecordedAudioMain(audioBlob);
                                      audioChunks = [];
                                      stream.getTracks().forEach( track => track.stop() ); // get all tracks from the MediaStream // stop each of them
                                  });

                                  // Cambiar el texto del botón y su función onclick
                                  var recordBtn = document.getElementById('record-btn-main');
                                  recordBtn.textContent = 'Detener Grabación';
                                  recordBtn.onclick = stopRecordingMain;
                              });
                      }

                      // Función para detener la grabación
                      function stopRecordingMain() {
                          mediaRecorder.stop();

                          // Restablecer el botón de grabación
                          var recordBtn = document.getElementById('record-btn-main');
                          recordBtn.innerHTML = "<i class='bi bi-mic fs-4' ></i>";
                          recordBtn.onclick = startRecordingMain;
                      }

                      // Función para subir el audio grabado
                      function uploadRecordedAudioMain(audioBlob) {
                          var loadingSpinner = document.getElementById('loading-spinner2');
                          var upload_audio = document.getElementById('upload_audio');
                          var recordBtn = document.getElementById('record-btn-main');

                          // Mostrar el spinner de carga
                          loadingSpinner.style.display = 'block';
                          upload_audio.style.display = 'none';
                          recordBtn.style.display = 'none';

                          var formData = new FormData();
                          formData.append('audio', audioBlob, 'grabacion.mp3');

                          // Enviar el audio grabado al controlador mediante fetch
                          fetch('/sttApi', {
                              method: 'POST',
                              body: formData,
                              headers: {
                                  'X-CSRF-TOKEN': '{{ csrf_token() }}', // Asegúrate de incluir el token CSRF de Laravel
                              },
                          })
                          .then(response => response.json())
                          .then(transcriptionText => {
                              // Ocultar el spinner de carga
                              loadingSpinner.style.display = 'none';
                              upload_audio.style.display = 'inline';
                              recordBtn.style.display = 'inline';
                              // Mostrar la transcripción
                              document.getElementById('promptInput').value = transcriptionText;
                          })
                          .catch(error => {
                              // Ocultar el spinner de carga
                              loadingSpinner.style.display = 'none';
                              upload_audio.style.display = 'inline';
                              recordBtn.style.display = 'inline';
                              console.error('Error al subir la grabación:', error);
                              alert('Error al subir la grabación.');
                          });
                      }
                    </script>
                  </div>
                    <input type="text" class="form-control" id="promptInput" placeholder="¿Que datos tiene la tabla (Proyecto/Material/Trabajador/TrabajadoresDelProyecto) ?">
                    <button id="startAssistantButton" class="btn btn-outline-secondary" type="button" onclick="startAssistant(this)"><i class="bi bi-arrow-up-square-fill fs-4"></i></button>
                    <div id="loading-spinner3" class="spinner-border" role="status" style="border-radius: 50%;display: none;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <script>
                      // Función para subir el audio grabado
                      function startAssistant(sendButton) {
                          var loadingSpinner = document.getElementById('loading-spinner3');
                          var text_prompt = document.getElementById('promptInput').value;
                          var sendButton = document.getElementById('record-btn-main');
                          var startAssistantButton = document.getElementById('startAssistantButton');

                          // Mostrar el spinner de carga
                          loadingSpinner.style.display = 'block';
                          sendButton.style.display = 'none';
                          startAssistantButton.style.display = 'none';

                          var formData = new FormData();
                          formData.append('texto', text_prompt);

                          // Enviar el audio grabado al controlador mediante fetch
                          fetch('/assistant', {
                              method: 'POST',
                              body: formData,
                              headers: {
                                  'X-CSRF-TOKEN': '{{ csrf_token() }}', // Asegúrate de incluir el token CSRF de Laravel
                              },
                          })
                          .then(response => response.json())
                          .then(assistantAnswer => {
                              // Ocultar el spinner de carga
                              loadingSpinner.style.display = 'none';
                              sendButton.style.display = 'inline';
                              startAssistantButton.style.display = 'inline';
                              // Mostrar la transcripción
                              // document.getElementById('divRespuesta').value = assistantAnswer;
                              alert(assistantAnswer)
                          })
                          .catch(error => {
                              // Ocultar el spinner de carga
                              loadingSpinner.style.display = 'none';
                              sendButton.style.display = 'inline';
                              startAssistantButton.style.display = 'inline';
                              console.error('Error al subir el text prompt:', error);
                              alert('Error al ejecutar el asistente.');
                          });
                      }
                    </script>
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

