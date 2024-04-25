<h2>Transcripción de Audio Local</h2>

<!-- Formulario para subir audio -->
<form id="upload-form2">
    <input type="file" id="audio-input2" accept=".mp3" />
    <button id="upload_audio2" type="button" onclick="uploadAudio()">Subir Audio</button>
</form>

<!-- Loading Spinner de Bootstrap -->
<div id="loading-spinner4" class="spinner-border" role="status" style="display: none;">
    <span class="visually-hidden">Cargando...</span>
</div>

<!-- Botón para grabar audio -->
<button id="record-btn2" onclick="startRecording()">Grabar Audio</button>

<!-- Contenedor para mostrar la transcripción -->
<div id="transcription2"></div>



<form action="/sttApi" method="POST" enctype="multipart/form-data" style="display:none">
    <!-- Campo oculto para el token CSRF -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    
    <input type="file" name="audio" accept="audio/*" required>
    <button type="submit">Subir Audio</button>
</form>


<script>
// Función para subir el audio
function uploadAudioLocal() {
    var audioInput = document.getElementById('audio-input2');
    var audioFile = audioInput.files[0];
    var loadingSpinner = document.getElementById('loading-spinner4');
    var upload_audio = document.getElementById('upload_audio2');
    var recordBtn = document.getElementById('record-btn2');

    // Mostrar el spinner de carga
    loadingSpinner.style.display = 'block';
    upload_audio.style.display = 'none';
    recordBtn.style.display = 'none';
    
    // Crear un FormData y agregar el archivo de audio
    var formData = new FormData();
    formData.append('audio', audioFile);

    // Enviar el FormData al controlador mediante fetch
    fetch('/localStt', {
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
        document.getElementById('transcription2').textContent = transcriptionText;
    })
    .catch(error => {
        // Ocultar el spinner de carga
        loadingSpinner.style.display = 'none';
        upload_audio.style.display = 'inline';
        recordBtn.style.display = 'inline';
        console.error('Error al subir el archivo:', error);
        alert('Error al subir el archivo.');
    });
}

// Variables para la grabación de audio
var mediaRecorder;
var audioChunks = [];

// Función para iniciar la grabación
function startRecordingLocal() {
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.start();

            mediaRecorder.addEventListener('dataavailable', event => {
                audioChunks.push(event.data);
            });

            mediaRecorder.addEventListener('stop', () => {
                var audioBlob = new Blob(audioChunks);
                uploadRecordedAudio(audioBlob);
                audioChunks = [];
                stream.getTracks().forEach( track => track.stop() ); // get all tracks from the MediaStream // stop each of them
            });

            // Cambiar el texto del botón y su función onclick
            var recordBtn = document.getElementById('record-btn2');
            recordBtn.textContent = 'Detener Grabación';
            recordBtn.onclick = stopRecording;
        });
}

// Función para detener la grabación
function stopRecordingLocal() {
    mediaRecorder.stop();

    // Restablecer el botón de grabación
    var recordBtn = document.getElementById('record-btn2');
    recordBtn.textContent = 'Grabar Audio';
    recordBtn.onclick = startRecording;
}

// Función para subir el audio grabado
function uploadRecordedAudioLocal(audioBlob) {
    var loadingSpinner = document.getElementById('loading-spinner4');
    var upload_audio = document.getElementById('upload_audio2');
    var recordBtn = document.getElementById('record-btn2');

    // Mostrar el spinner de carga
    loadingSpinner.style.display = 'block';
    upload_audio.style.display = 'none';
    recordBtn.style.display = 'none';

    var formData = new FormData();
    formData.append('audio', audioBlob, 'grabacion.mp3');

    // Enviar el audio grabado al controlador mediante fetch
    fetch('/LocalStt', {
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
        document.getElementById('transcription').textContent = transcriptionText;
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