<h2>Convertir texto en audio mediante WebSpeech</h2>
<p>El audio será reproducido al generarlo.</p>
<textarea id="textoParaAudio" rows="4" cols="50" placeholder="Introduce el texto aquí..."></textarea>
<br>
<button onclick="convertirTextoEnAudio()">Generar Audio</button>

<script>
    function convertirTextoEnAudio() {
        // Obtiene el texto del área de texto
        var texto = document.getElementById("textoParaAudio").value;
        
        // Verifica si el navegador soporta speechSynthesis
        if ('speechSynthesis' in window) {
            // Crea una nueva instancia de SpeechSynthesisUtterance
            var mensaje = new SpeechSynthesisUtterance(texto);
            
            // Opcional: Configura el idioma del mensaje
            mensaje.lang = "es-ES";
            
            // Reproduce el mensaje
            window.speechSynthesis.speak(mensaje);
        } else {
            // Si speechSynthesis no está soportado
            alert("Lo siento, tu navegador no soporta la síntesis de voz.");
        }
    }
</script>