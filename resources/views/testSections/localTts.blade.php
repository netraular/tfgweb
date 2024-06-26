<h2>Convertir texto en audio mediante servidor local</h2>
<textarea id="textoParaAudioLocal" rows="4" cols="50" placeholder="Introduce el texto aquí..."></textarea>
<br>
<button id="generarAudioBtn2" onclick="convertirTextoEnAudioLocal()">Generar Audio</button>

<!-- Loading Spinner de Bootstrap -->
<div id="loading-spinner4" class="spinner-border" role="status" style="display: none;">
    <span class="visually-hidden">Cargando...</span>
</div>

<br>
<div id="audioContainer2"></div> <!-- Contenedor para el reproductor de audio -->


<script>
function convertirTextoEnAudioLocal() {
    var texto = document.getElementById('textoParaAudioLocal').value;
    var loadingSpinner = document.getElementById('loading-spinner4');
    var generarAudioBtn = document.getElementById('generarAudioBtn2');
    
    // Mostrar el spinner de carga
    loadingSpinner.style.display = 'block';
    generarAudioBtn.style.display = 'none';

    // Crear un objeto FormData para enviar el texto
    var formData = new FormData();
    formData.append('texto', texto);

    // Realizar la llamada AJAX
    fetch('/ttsLocal', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}', // Asegúrate de incluir el token CSRF de Laravel
        },
    })
    .then(response => response.blob()) // Convertir la respuesta en un Blob
    .then(blob => {
        // Ocultar el spinner de carga
        loadingSpinner.style.display = 'none';
        generarAudioBtn.style.display = 'inline';

        // Crear un URL para el Blob
        var url = window.URL.createObjectURL(blob);
        
        // Buscar el contenedor donde se mostrará el reproductor de audio
        var audioContainer = document.getElementById('audioContainer2');
        
        // Limpiar el contenedor por si ya había un reproductor de audio anterior
        audioContainer.innerHTML = '';
        
        // Crear un nuevo elemento <audio> con controles y la fuente del audio
        var audioElement = document.createElement('audio');
        audioElement.controls = true;
        audioElement.src = url;
        
        // Insertar el elemento <audio> en el contenedor
        audioContainer.appendChild(audioElement);
        
        // Opcional: reproducir el audio automáticamente
        audioElement.play();
    })
    .catch(error => {
        // Ocultar el spinner de carga
        loadingSpinner.style.display = 'none';
        generarAudioBtn.style.display = 'inline';
        console.error('Error:', error)
    });
}
</script>