<h2>Reconocimiento de Voz a Texto mediante WebSpeech</h2>
<button id="start-btn">Iniciar Escucha</button>
<button id="stop-btn">Detener Escucha</button>
<p id="transcript">Transcripción: </p>

<script>
    // Verificar si el navegador soporta la API de Web Speech
    if (!('webkitSpeechRecognition' in window)) {
        alert('Lo siento, tu navegador no soporta la API de reconocimiento de voz.');
    } else {
        // Crear una nueva instancia de webkitSpeechRecognition
        var recognition = new webkitSpeechRecognition();
        recognition.lang = 'es-ES'; // Establecer el idioma
        recognition.continuous = true; // Continuar escuchando incluso después de capturar la voz
        recognition.interimResults = true; // Resultados intermedios

        // Referencias a elementos del DOM
        var startBtn = document.getElementById('start-btn');
        var stopBtn = document.getElementById('stop-btn');
        var transcript = document.getElementById('transcript');

        // Iniciar el reconocimiento de voz
        startBtn.onclick = function() {
            recognition.start();
        };

        // Detener el reconocimiento de voz
        stopBtn.onclick = function() {
            recognition.stop();
        };

        // Manejar el evento de resultado del reconocimiento de voz
        recognition.onresult = function(event) {
            var interimTranscript = '';
            for (var i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    transcript.innerHTML += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }
            // Mostrar la transcripción en la página
            transcript.innerHTML = 'Transcripción: ' + interimTranscript;
        };

        // Manejar errores
        recognition.onerror = function(event) {
            console.log('Error en el reconocimiento de voz: ', event.error);
        };
    }
</script>