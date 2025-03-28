@extends('layouts.app')

@section('content')

<div id="assistantLog" class="container">
    <table class="table table-striped table-hover" id="assistantLogTable">
        <thead></thead> <!-- Added thead for structure, although not strictly needed here -->
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
        <!-- Botón para grabar audio (puede permanecer oculto si no se usa activamente) -->
        <button id="upload_audio" hidden onclick="/* uploadAudio() */">Subir Audio</button>
    </div>
    <input type="text" class="form-control" id="promptInput" placeholder="Dime el nombre de los primeros 10 trabajadores. ¿Cuantos proyectos/materiales hay?">
    <button id="startAssistantButton" class="btn btn-outline-secondary" type="button" onclick="startAssistant()"><i class="bi bi-arrow-up-square-fill fs-4"></i></button>
    <div id="loading-spinner3" class="spinner-border" role="status" style="border-radius: 50%;display: none;">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>


<script>
    // --- Audio scripts (Mantener como estaban - la llamada fetch fallará ahora) ---
    // Variables para la grabación de audio
    var mediaRecorder;
    var audioChunks = [];

    // Función para iniciar la grabación
    function startRecordingMain() {
        // No hacer nada si el botón está deshabilitado (aunque el evento onclick no debería dispararse)
        if(document.getElementById('record-btn-main').disabled) {
             console.warn('La grabación de audio está deshabilitada.');
             return;
        }
        // ... (resto del código de startRecordingMain como estaba, aunque no se llamará si el botón está disabled)
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
        recordBtn.innerHTML = "<i class='bi bi-mic fs-4' ></i>"; // O mantener el icono mute si prefieres
        recordBtn.onclick = startRecordingMain;
    }

    // Función para subir el audio grabado
    function uploadRecordedAudioMain(audioBlob) {
        var loadingSpinner = document.getElementById('loading-spinner2');
        var upload_audio = document.getElementById('upload_audio');
        var recordBtn = document.getElementById('record-btn-main');

        // Mostrar el spinner de carga
        loadingSpinner.style.display = 'block';
        recordBtn.style.display = 'none';

        var formData = new FormData();
        formData.append('audio', audioBlob, 'grabacion.mp3');

        // Enviar el audio grabado al controlador mediante fetch
        // ESTA LLAMADA FALLARÁ AHORA CON UN 404 (Not Found) porque la ruta estará comentada
        fetch('/sttLocal', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        })
        .then(response => {
            if (!response.ok) {
                // Se espera que falle aquí porque la ruta no existe
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
         })
        .then(data => {
             if (data && data.transcription) {
                 document.getElementById('promptInput').value = data.transcription;
             } else {
                 console.warn('Received unexpected data from STT:', data);
                 document.getElementById('promptInput').value = '';
                 alert('No se pudo obtener la transcripción.');
             }
        })
        .catch(error => {
            // El error será capturado aquí
            console.error('Error al subir o procesar la grabación (esperado por ruta deshabilitada):', error);
            alert('Error al procesar el audio: La función de transcripción local está deshabilitada. ' + error.message); // Mensaje más específico
        })
        .finally(() => {
            // Ocultar el spinner de carga y mostrar botón en cualquier caso
            loadingSpinner.style.display = 'none';
            recordBtn.style.display = 'inline'; // Mostrar botón de nuevo (aunque sigue deshabilitado)
        });
    }

</script>
<script>
    // --- Assistant scripts (MODIFIED) ---

    function addNewLogTable(text_prompt) {
        var logTableBody = document.getElementById('assistantLogTable').getElementsByTagName('tbody')[0];

        // Crear una nueva fila contenedora en el tbody principal
        var containerRow = logTableBody.insertRow(0); // Insert at the top
        var containerCell = containerRow.insertCell();
        containerCell.colSpan = 1; // Adjust if your main table has more columns initially

        // Crear la tabla anidada para Q/A/E dentro de la celda contenedora
        var nestedTable = document.createElement('table');
        nestedTable.className = 'table mb-0'; // Add bootstrap classes if desired
        containerCell.appendChild(nestedTable);

        // Fila para 'Q:'
        var rowQ = nestedTable.insertRow();
        var cellQLabel = rowQ.insertCell();
        cellQLabel.textContent = 'Q:';
        cellQLabel.style.width = '2em'; // Fixed width for labels
        var cellQText = rowQ.insertCell();
        cellQText.textContent = text_prompt;

        // Fila para 'A:' (inicialmente vacía)
        var rowA = nestedTable.insertRow();
        var cellALabel = rowA.insertCell();
        cellALabel.textContent = 'A:';
        var cellAText = rowA.insertCell();
        cellAText.innerHTML = '<i>Procesando...</i>'; // Placeholder

        // Fila para 'E:' (Resultados/Errores, inicialmente vacía)
        var rowE = nestedTable.insertRow();
        var cellELabel = rowE.insertCell();
        cellELabel.textContent = 'E:';
        var cellEText = rowE.insertCell();
        // No placeholder needed here, will be filled later

        // Devolver la tabla anidada para poder añadirle la respuesta/resultados
        return nestedTable;
    }

    function addAnswerToLogTable(logTable, assistantResponse) {
        // logTable es la tabla anidada devuelta por addNewLogTable
        var rowA = logTable.rows[1]; // Fila 'A:'
        var cellAText = rowA.cells[1]; // Segunda celda de la fila 'A:'

        var rowE = logTable.rows[2]; // Fila 'E:'
        var cellEText = rowE.cells[1]; // Segunda celda de la fila 'E:'
        cellEText.innerHTML = ''; // Clear previous content if any

        // Check if the response indicates an error from the backend
        if (assistantResponse.error) {
            cellAText.innerHTML = `<span class="text-danger"><b>Error</b></span>`;
            cellEText.innerHTML = `<span class="text-danger">${assistantResponse.error} ${assistantResponse.details ? `(${assistantResponse.details})` : ''}</span>`;
        }
        // Handle successful response (even if the answer is "I don't know")
        else {
            // Display the answer (e.g., "SELECT ...", "I don't know", etc.)
            cellAText.innerHTML = `<b>${assistantResponse.answer || '(Respuesta no proporcionada)'}</b>`;

            // Process and display results ('E:' row)
            const results = assistantResponse.results;

            if (results === null || results === undefined) {
                cellEText.innerHTML = '<i>(Sin resultados)</i>';
            } else if (typeof results === 'string') {
                // Attempt to parse if it looks like JSON, otherwise display as string
                try {
                    // Check if it starts with [ and ends with ] or starts with { and ends with }
                    const trimmedResults = results.trim();
                    if ((trimmedResults.startsWith('[') && trimmedResults.endsWith(']')) || (trimmedResults.startsWith('{') && trimmedResults.endsWith('}'))) {
                        const parsedResults = JSON.parse(results);

                        // Check if it's an array (expected for SQL results) and not empty
                        if (Array.isArray(parsedResults) && parsedResults.length > 0) {
                            // Build HTML table for results
                            let tableHtml = '<table class="table table-sm table-bordered table-striped"><thead><tr>';
                            const keys = Object.keys(parsedResults[0]);
                            keys.forEach(key => { tableHtml += `<th>${escapeHtml(key)}</th>`; });
                            tableHtml += '</tr></thead><tbody>';
                            parsedResults.forEach(obj => {
                                tableHtml += '<tr>';
                                keys.forEach(key => {
                                    tableHtml += `<td>${escapeHtml(obj[key])}</td>`;
                                });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody></table>';
                            cellEText.innerHTML = tableHtml;
                        } else if (Array.isArray(parsedResults) && parsedResults.length === 0) {
                             cellEText.innerHTML = '<i>(Consulta exitosa, sin filas devueltas)</i>';
                        }
                         else {
                            // It was valid JSON, but not the expected array format, display as text
                            cellEText.textContent = results;
                        }
                    } else {
                        // Doesn't look like JSON, display as plain text (e.g., "Query voided", "SQL query failed", truncated data)
                        cellEText.textContent = results;
                    }
                } catch (e) {
                    // JSON parsing failed, display as plain text
                    cellEText.textContent = results;
                }
            } else {
                // Should ideally not happen if backend sends null, string, or JSON string
                cellEText.textContent = '[Resultados en formato inesperado]';
                console.warn("Unexpected format for results:", results);
            }
        }
    }



    // --- Event Listener and Main Assistant Function (MODIFIED) ---

    document.getElementById('promptInput').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
             // Prevent default form submission if inside a form
             event.preventDefault();
             // Trigger the assistant function
             startAssistant();
        }
    });

    var assistantRunning = false;

    function startAssistant() {
        if (assistantRunning) {
            // Maybe provide feedback instead of alert, e.g., disable button temporarily
            console.log('Assistant is already running.');
            return;
        }

        var promptInput = document.getElementById('promptInput');
        var text_prompt = promptInput.value.trim(); // Trim whitespace

        if (!text_prompt) {
            // Don't run if prompt is empty
            promptInput.focus();
            return;
        }

        var loadingSpinner = document.getElementById('loading-spinner3');
        var recordButton = document.getElementById('record-btn-main'); // Corrected variable name
        var startAssistantButton = document.getElementById('startAssistantButton');

        // UI updates: Show spinner, disable buttons
        loadingSpinner.style.display = 'block';
        recordButton.disabled = true;
        startAssistantButton.disabled = true;
        promptInput.disabled = true;
        assistantRunning = true;

        // Add placeholder log entry
        var currentLogTable = addNewLogTable(text_prompt);

        var formData = new FormData();
        formData.append('texto', text_prompt);

        // Enviar el texto al controlador mediante fetch
        fetch('/assistant', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json' // Explicitly accept JSON
            },
        })
        .then(response => {
            // Check if the response status is OK (e.g., 200)
            // Even if it's a 500 error, we might get JSON back if the backend is fixed
            if (!response.ok) {
                 // Try to parse error JSON from backend if possible, otherwise throw generic error
                 return response.json().then(errorData => {
                    // Throw an object that includes the error data and status
                    throw { status: response.status, data: errorData, message: `HTTP error ${response.status}` };
                 }).catch(parseError => {
                     // If response.json() itself failed (e.g., HTML error page)
                     throw { status: response.status, message: `HTTP error ${response.status}. Failed to parse response.` };
                 });
            }
            // If response is OK, parse the JSON body
            return response.json();
        })
        .then(assistantResponse => {
            // Backend returned a successful (e.g., 200) JSON response
            // This JSON might contain an 'answer' or an 'error' key from the controller logic
            addAnswerToLogTable(currentLogTable, assistantResponse);
        })
        .catch(error => {
            // Handles network errors or errors thrown from the .then block (like non-ok responses)
            console.error('Error during assistant request:', error);

            // Create a response object to display the error in the log table
            const errorResponse = {
                error: `Error ${error.status || 'desconocido'}`,
                details: error.data?.error || error.data?.details || error.message || 'No se pudo comunicar con el servidor o procesar la respuesta.'
            };
            addAnswerToLogTable(currentLogTable, errorResponse);

            // Show the generic alert ONLY if really necessary, prefer displaying in log
            // alert('Ocurrió un error. Por favor, revisa el log.');
        })
        .finally(() => {
            // This block runs whether the fetch succeeded or failed

            // UI updates: Hide spinner, re-enable buttons
            loadingSpinner.style.display = 'none';
            recordButton.disabled = false;
            startAssistantButton.disabled = false;
            promptInput.disabled = false;
            assistantRunning = false; // Allow new requests
            promptInput.focus(); // Set focus back to input
        });

        // Limpiar el promptInput después de iniciar la solicitud (opcional, puede ser mejor limpiar al final)
        promptInput.value = '';
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert("Aviso: El asistente se ha configurado con recursos limitados. Las funciones locales de Texto-a-Voz (TTS) y Voz-a-Texto (STT) están deshabilitadas y se usará una api gratuita en vez de ejecución de ollama en local.");

        // Opcional: Deshabilitar visualmente el botón de grabar si no se hizo en HTML
        const recordBtn = document.getElementById('record-btn-main');
        if (recordBtn) {
            recordBtn.disabled = true;
            recordBtn.title = "Función deshabilitada por recursos limitados";
            // Cambiar el icono a 'mute' para indicar que está deshabilitado (opcional)
            const icon = recordBtn.querySelector('i');
            if (icon) {
                icon.classList.remove('bi-mic');
                icon.classList.add('bi-mic-mute');
            }
        }
    });
</script>
@endsection