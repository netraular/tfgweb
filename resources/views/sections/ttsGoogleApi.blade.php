<h2>Convertir texto en audio mediante server API</h2>
<textarea id="textoParaAudio" rows="4" cols="50" placeholder="Introduce el texto aquí..."></textarea>
<br>
<button onclick="convertirTextoEnAudioAPI()">Generar Audio</button>
<br>
<div id="audioContainer"></div> <!-- Contenedor para el reproductor de audio -->


<script>
function convertirTextoEnAudioAPI() {
    var texto = document.getElementById('textoParaAudio').value;

    // Crear un objeto FormData para enviar el texto
    var formData = new FormData();
    formData.append('texto', texto);

    // Realizar la llamada AJAX
    fetch('/generar-audio', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}', // Asegúrate de incluir el token CSRF de Laravel
        },
    })
    .then(response => response.blob()) // Convertir la respuesta en un Blob
    .then(blob => {
        // Crear un URL para el Blob
        var url = window.URL.createObjectURL(blob);
        
        // Buscar el contenedor donde se mostrará el reproductor de audio
        var audioContainer = document.getElementById('audioContainer');
        
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
    .catch(error => console.error('Error:', error));
}
</script>