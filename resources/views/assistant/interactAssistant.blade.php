@extends('layouts.app')

@section('content')
    

<div id="assistantLog" class="container">
    <table class="table table-striped table-hover" id="assistantLogTable">

        <tbody>
            <!-- Filas se añadirán dinámicamente aquí -->
        </tbody>
    </table>
</div>
<div class="input-group" style="position:fixed;bottom:0;padding:20px;max-width: 800px;padding-right: 40px;">
    <div>
    <button id="record-btn-main" class="btn btn-outline-secondary" type="button" onclick="startRecordingMain()"><i class="bi bi-mic fs-4" ></i></button>
    <!-- Loading Spinner de Bootstrap -->
    <div id="loading-spinner2" class="spinner-border" role="status" style="display: none;">
        <span class="visually-hidden">Cargando...</span>
    </div>

    <!-- Botón para grabar audio -->
    <button id="upload_audio" hidden onclick="uploadAudio()">Subir Audio</button>

    </div>
    <input type="text" class="form-control" id="promptInput" placeholder="¿Que datos tiene la tabla (Proyecto/Material/Trabajador/TrabajadoresDelProyecto) ?">
    <button id="startAssistantButton" class="btn btn-outline-secondary" type="button" onclick="startAssistant(this)"><i class="bi bi-arrow-up-square-fill fs-4"></i></button>
    <div id="loading-spinner3" class="spinner-border" role="status" style="border-radius: 50%;display: none;">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>


<script>
    //Audio scripts

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
<script>
    //Assistant scripts

function addNewLogTable(text_prompt) {
    var logTable = document.getElementById('assistantLogTable').getElementsByTagName('tbody')[0];

    // Crear una nueva fila
    var newRow = logTable.insertRow();

    // Crear una celda para la nueva fila
    var cell = newRow.insertCell();

    // Crear la tabla dentro de la celda
    var table = document.createElement('table');
    cell.appendChild(table); // Añadir la tabla a la celda


    // Crear una nueva fila para 'Q:' y text_prompt en la segunda columna
    var rowQ = table.insertRow();
    var cellQ = rowQ.insertCell();
    cellQ.textContent = 'Q:';
    var cellQ2 = rowQ.insertCell();
    cellQ2.textContent = text_prompt;

    // Crear una nueva fila para 'A:'
    var rowA = table.insertRow();
    var cellA = rowA.insertCell();
    cellA.textContent = 'A:';

    // Crear una nueva fila para 'E:'
    var rowE = table.insertRow();
    var cellE = rowE.insertCell();
    // cellE.textContent = 'E:';

    return table; // Retornar la tabla creada para futuras referencias si es necesario
}


function addAnswerToLogTable(logTable, assistantAnswer) {
    // Asumiendo que logTable es una referencia a la tabla donde deseas añadir la respuesta del asistente
    
    // Obtener la segunda fila (índice 1, ya que los índices de las filas comienzan desde 0)
    var rowA = logTable.rows[1]; // Segunda fila para 'A:'
    
    // Insertar el contenido de assistantAnswer en la segunda columna (índice 1)
    var cellA2 = rowA.insertCell(1);
    cellA2.innerHTML = '<b>' + assistantAnswer[0] + '</b>';

    // Obtener la tercera fila (índice 2) para 'E:'
    var rowE = logTable.rows[2];

    // Limpiar la celda antes de insertar contenido nuevo
    var cellE2 = rowE.insertCell(1);
    cellE2.innerHTML = ''; // Limpiar contenido previo
    
    // Verificar si assistantAnswer[1] es un string o un objeto
    if (typeof assistantAnswer[1] === 'string') {
        // Si assistantAnswer[1] es un string, simplemente asignarlo como texto
        cellE2.textContent = assistantAnswer[1];
    } else if (Array.isArray(assistantAnswer[1])) {
        // Si assistantAnswer[1] es un array, construir la tabla dinámicamente
        var tableHtml = '<table border="1"><thead><tr>';

        // Obtener las claves para las cabeceras de la tabla
        var keys = Object.keys(assistantAnswer[1][0]);

        // Construir las cabeceras de la tabla con las claves
        keys.forEach(function(key) {
            tableHtml += '<th>' + key + '</th>';
        });

        tableHtml += '</tr></thead><tbody>';

        // Iterar sobre cada objeto en assistantAnswer[1] para construir las filas de datos
        assistantAnswer[1].forEach(function(obj) {
            tableHtml += '<tr>';

            // Iterar sobre las claves y obtener el valor correspondiente del objeto
            keys.forEach(function(key) {
                tableHtml += '<td>' + obj[key] + '</td>';
            });

            tableHtml += '</tr>';
        });

        tableHtml += '</tbody></table>';

        // Insertar la tabla construida en la celda
        cellE2.innerHTML = tableHtml;
    }
}








// Función para iniciar el asistente al presionar Enter en el input
document.getElementById('promptInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        startAssistant();
    }
});

// Variable para controlar si ya se está ejecutando el asistente
var assistantRunning = false;

function startAssistant(sendButton) {
    if (assistantRunning) {
        alert('Espera a que termine la ejecución actual.');
        return;
    }

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
    // Agregar la entrada al log
    logTable=addNewLogTable(text_prompt); // Primera fila con el texto de prompt
    // Marcar que el asistente está en ejecución
    assistantRunning = true;

    // Enviar el texto al controlador mediante fetch
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

        // Agregar la respuesta del asistente como segunda fila en la tabla de log
        addAnswerToLogTable(logTable,assistantAnswer);

        // Reiniciar la bandera de ejecución del asistente
        assistantRunning = false;
    })
    .catch(error => {
        // Ocultar el spinner de carga
        loadingSpinner.style.display = 'none';
        sendButton.style.display = 'inline';
        startAssistantButton.style.display = 'inline';
        console.error('Error al subir el text prompt:', error);
        alert('Error al ejecutar el asistente.');
        assistantRunning = false;
    });

    // Limpiar el promptInput después de enviar la solicitud
    document.getElementById('promptInput').value = '';
}


</script>

@endsection