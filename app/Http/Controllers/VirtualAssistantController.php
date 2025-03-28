<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simulacion;
use App\Models\Trabajador;
use App\Models\Proyecto;
use App\Models\Material;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\assistantHistory;
use App\Models\llmTest;
use App\Models\llmTestAnswers;

use Illuminate\Support\Facades\DB;

class VirtualAssistantController extends Controller
{
    //
    public function testMenu(Request $request){
        $preguntas=['1','dos','3tres'];
        $preguntas1=Simulacion::select('pregunta')->get()->toArray();
        $preguntas2=["trabajador"=>['nombre','apellidos','edad','sexo'],"proyecto"=>['nombre','descripcion','sector','ubicacion','fecha'],"material"=>['nombre','cantidad','proyecto']];
        $preguntas=[$preguntas1,$preguntas2];
        switch($request->questionType){
            case 1:
                //Direct question with pre selected answer
                $inputText=$request->inputText1;
                $output=Simulacion::select('respuesta')->where('pregunta','=',$inputText)->first();
                return view('testMenu',compact('preguntas','inputText','output'));
                break;
            case 2:
                $inputText='';
                $output["respuesta"]='';
                switch($request->inputText1){
                    case "trabajador":
                        if(isset($request->inputText2_trabajador)){
                            $inputText="De la tabla <b>[ $request->inputText1 ]</b> dame la columna <b>[ $request->inputText2_trabajador ]</b>";

                            $output=Trabajador::select($request->inputText2_trabajador)->where($request->inputText3_trabajador,'=',$request->valueEqual)->get();
                            $output['respuesta']=array_values($output->toArray());
                        }
                    break;
                    case "proyecto":
                        if(isset($request->inputText2_proyecto)){
                            $inputText="De la tabla <b>[ $request->inputText1 ]</b> dame la columna <b>[ $request->inputText2_proyecto ]</b>";
                            $output=Proyecto::select($request->inputText2_proyecto)->where($request->inputText3_trabajador,'=',$request->valueEqual)->get();
                            $output['respuesta']=array_values($output->toArray())[0];
                        }
                    break;
                    case "material":
                        if(isset($request->inputText2_material)){
                            $inputText="De la tabla <b>[ $request->inputText1 ]</b> dame la columna <b>[ $request->inputText2_material ]</b>";
                            $output=Material::select($request->inputText2_material)->where($request->inputText3_trabajador,'=',$request->valueEqual)->get();
                            $output['respuesta']=array_values($output->toArray())[0];
                        }
                    break;
                }
                return view('testMenu',compact('preguntas','inputText','output'));
                break;
            default:
                //Initial page load
                return view('testMenu',compact('preguntas'));
                break;
        }
    }

    // public function main(Request $request)
    // {
    //     $texto = $request->texto;

    //     $assistantLog = new AssistantHistory;
    //     $assistantLog->question = $request->texto;
    //     $assistantLog->save();

    //     $scriptPath = resource_path('scripts/python/groq_llm.py'); 
    //     $apiKey = env('GROQ_API_KEY');

    //     if (!$apiKey) {
    //          report(new \Exception('GROQ_API_KEY no encontrada en el archivo .env'));
    //          return response()->json(['error' => 'Error de configuración del servidor.'], 500);
    //     }

    //     $process = new Process(
    //         [
    //             'python3',
    //             $scriptPath,
    //             escapeshellarg($texto),
    //         ],
    //         null,
    //         ['GROQ_API_KEY' => $apiKey]
    //     );



    //     try {
    //         $startTimer = microtime(true);
    //         $process->mustRun(); // mustRun() lanza ProcessFailedException automáticamente si falla
    //         $time_elapsed_secs = microtime(true) - $startTimer;
    //         $answer = $process->getOutput();

    //         // Limpiar saltos de línea (opcional, depende de si los necesitas)
    //         $answer = trim(str_replace("\n", " ", $answer)); // trim() elimina espacios/saltos al inicio/final

    //         $assistantLog->answerTime = $time_elapsed_secs;

    //         // Truncar si es necesario ANTES de intentar guardar
    //         if (mb_strlen($answer) >= 1000) { // Usa mb_strlen para strings multibyte
    //             $answer = mb_substr($answer, 0, 995) . '...'; // Trunca dejando espacio para '...'
    //             $assistantLog->extra = "Respuesta original truncada."; // Añade una nota
    //         }
    //         $assistantLog->answer = $answer;

    //         $results = null; // Inicializa results

    //         if ($answer !== "I don't know." && mb_strlen($answer) < 1000) { // Comprueba de nuevo por si fue truncado a vacío
    //             $sql = $answer; // Asume que la respuesta es SQL
    //             if ($this->checkSqlIsSafe($sql)) { // Asegúrate que esta función es robusta
    //                 try {
    //                     // ¡¡PRECAUCIÓN EXTREMA AL EJECUTAR SQL GENERADO POR IA!!
    //                     // Considera usar bindings si es posible, o una validación MUY estricta.
    //                     $resultsData = DB::select($sql);
    //                     $resultsJson = json_encode($resultsData);

    //                     if (mb_strlen($resultsJson) >= 1000) {
    //                         $results = mb_substr($resultsJson, 0, 995) . '...';
    //                         $assistantLog->extra = ($assistantLog->extra ?? '') . " Resultados JSON truncados.";
    //                     } else {
    //                          $results = $resultsJson;
    //                     }
    //                     $assistantLog->results = $results;

    //                 } catch (\Illuminate\Database\QueryException $e) {
    //                     // Error específico de base de datos
    //                     report($e); // Reporta el error real
    //                     $results = "SQL query failed: " . $e->getMessage();
    //                     // Limita la longitud del mensaje de error si es necesario
    //                      if (mb_strlen($results) >= 1000) {
    //                          $results = mb_substr($results, 0, 995) . '...';
    //                      }
    //                     $assistantLog->results = $results; // Guarda el mensaje de error

    //                 } catch (\Throwable $e) {
    //                     // Otro tipo de error
    //                     report($e);
    //                     $results = "An unexpected error occurred during SQL execution.";
    //                     $assistantLog->results = $results;
    //                 }
    //             } else {
    //                 $results = "Query voided (unsafe)";
    //                 $assistantLog->extra = ($assistantLog->extra ?? '') . " " . $results;
    //             }
    //         } else {
    //             // Si la respuesta fue "I don't know." o fue truncada antes
    //             $assistantLog->extra = ($assistantLog->extra ?? '') . " No SQL query executed.";
    //         }

    //         $assistantLog->save(); // Guarda la respuesta, resultados, tiempo, etc.

    //         // Devuelve la respuesta y los resultados (o mensajes de error)
    //         return response()->json([
    //             'answer' => $assistantLog->answer, // Devuelve la respuesta (posiblemente truncada)
    //             'results' => $results // Devuelve los resultados JSON, mensaje de error, o null
    //         ]);

    //     } catch (ProcessFailedException $exception) {
    //         // El proceso Python falló
    //         report($exception); // Reporta la excepción completa

    //         // Guarda el error en el log
    //         $assistantLog->answer = "Failed to execute script.";
    //         // Guarda la salida de error del script si está disponible
    //         $assistantLog->extra = "Error: " . $exception->getProcess()->getErrorOutput();
    //          if (mb_strlen($assistantLog->extra) >= 1000) {
    //              $assistantLog->extra = mb_substr($assistantLog->extra, 0, 995) . '...';
    //          }
    //         $assistantLog->save();

    //         return response()->json([
    //              'error' => 'El script del asistente falló.',
    //              'details' => $exception->getProcess()->getErrorOutput() // Opcional: no exponer detalles sensibles en producción
    //              ], 500);
    //     }
    // }

    public function main(Request $request)
    {
        $texto = $request->texto;
    
        $assistantLog = new AssistantHistory;
        $assistantLog->question = $request->texto;
        // Consider saving only *after* successful script execution or at the very end
        // $assistantLog->save(); // Maybe move this
    
        $scriptPath = resource_path('scripts/python/groq_llm.py'); // Ensure this path is correct
        $apiKey = env('GROQ_API_KEY');
    
        if (!$apiKey) {
             report(new \Exception('GROQ_API_KEY no encontrada en el archivo .env'));
             // Save the log indicating the config error before returning
             $assistantLog->answer = "Server configuration error.";
             $assistantLog->extra = "GROQ_API_KEY missing.";
             $assistantLog->save();
             return response()->json(['error' => 'Error de configuración del servidor.'], 500);
        }
    
        $process = new Process(
            [
                'python3',
                $scriptPath,
                escapeshellarg($texto), // Use escapeshellarg like before
            ],
            null,
            ['GROQ_API_KEY' => $apiKey]
        );
    
        $answer = null;
        $results = null;
        $time_elapsed_secs = 0;
    
        try {
            $startTimer = microtime(true);
            // Use mustRun() inside try-catch or run() + check + throw inside try-catch
            $process->mustRun(); // This throws ProcessFailedException if it fails
            $time_elapsed_secs = microtime(true) - $startTimer;
            $answer = $process->getOutput();
    
            // Clean and prepare answer
            $answer = trim(str_replace("\n", " ", $answer)); // Use trim like before
            $assistantLog->answerTime = $time_elapsed_secs;
    
            // Use mb_strlen and better truncation
            if (mb_strlen($answer) >= 1000) {
                $answer = mb_substr($answer, 0, 995) . '...';
                $assistantLog->extra = "Respuesta original truncada.";
            }
            $assistantLog->answer = $answer; // Set answer on the log
    
    
            // --- SQL Execution Part (moved inside try if Python succeeded) ---
            if ($answer !== "I don't know." && mb_strlen($answer) < 1000 && $answer !== (mb_substr($answer, 0, 995) . '...')) { // Check it wasn't truncated to empty/useless
                $sql = $answer;
                if ($this->checkSqlIsSafe($sql)) {
                    try {
                        $resultsData = DB::select($sql);
                        $resultsJson = json_encode($resultsData);
    
                        if (mb_strlen($resultsJson) >= 1000) {
                            $results = mb_substr($resultsJson, 0, 995) . '...';
                            $assistantLog->extra = ($assistantLog->extra ?? '') . " Resultados JSON truncados.";
                        } else {
                             $results = $resultsJson;
                        }
                        $assistantLog->results = $results;
    
                    } catch (\Illuminate\Database\QueryException $e) {
                        report($e); // Log the DB error
                        $errorMsg = "SQL query failed: " . $e->getMessage();
                         if (mb_strlen($errorMsg) >= 1000) {
                             $errorMsg = mb_substr($errorMsg, 0, 995) . '...';
                         }
                        $results = $errorMsg; // Provide error detail in results
                        $assistantLog->results = $results;
    
                    } catch (\Throwable $e) { // Catch other potential errors during DB interaction
                        report($e);
                        $results = "An unexpected error occurred during SQL execution.";
                        $assistantLog->results = $results;
                    }
                } else {
                    $results = "Query voided (unsafe)";
                    $assistantLog->extra = ($assistantLog->extra ?? '') . " " . $results;
                    $assistantLog->results = null; // Or set results field accordingly
                }
            } else {
                 $assistantLog->extra = ($assistantLog->extra ?? '') . " No SQL query executed (Answer was 'I don't know' or truncated).";
                 $results = null; // Ensure results is null if no query run
            }
            // --- End SQL Execution Part ---
    
            $assistantLog->save(); // Save everything gathered successfully
    
            // Return structured JSON object
            return response()->json([
                'answer' => $assistantLog->answer, // Return the processed answer from the log
                'results' => $results           // Return results (JSON, error message, or null)
            ]);
    
        } catch (ProcessFailedException $exception) {
            // *** CATCH THE PYTHON SCRIPT ERROR HERE ***
            report($exception);
    
            $assistantLog->answerTime = microtime(true) - $startTimer; // Log time even if failed
            $assistantLog->answer = "Failed to execute script.";
            $errorOutput = $exception->getProcess()->getErrorOutput();
            $assistantLog->extra = "Error: " . (mb_strlen($errorOutput) >= 900 ? mb_substr($errorOutput, 0, 895) . '...' : $errorOutput); // Truncate error output if needed
            $assistantLog->save(); // Save the failure state
    
            // *** Return a JSON error response ***
            return response()->json([
                 'error' => 'El script del asistente falló.',
                 // Avoid sending raw error output to the client in production
                 // 'details' => $exception->getProcess()->getErrorOutput()
                 'details' => 'Server-side script execution failed.' // Generic message for client
                 ], 500);
        } catch (\Throwable $e) {
            // Catch any other unexpected errors during the process
            report($e);
            // Save minimal error state if possible
            if ($assistantLog->exists) { // Check if already saved once
               $assistantLog->extra = ($assistantLog->extra ?? '') . ' Unexpected controller error: ' . $e->getMessage();
               $assistantLog->save();
            } else {
                // Log initial question if possible before failing hard
                 $assistantLog->answer = "Unexpected controller error.";
                 $assistantLog->extra = $e->getMessage();
                 $assistantLog->save();
            }
            return response()->json(['error' => 'An unexpected server error occurred.'], 500);
        }
    }

    public function interactAssistant(Request $request){

        return view('assistant.interactAssistant');
    }

    public function testLlm($llmName){
        $questions=llmTest::get();//where('id',">=",553)->
        foreach($questions as $question){
            $assistantLog = new llmTestAnswers;
            $assistantLog->question_id=$question->id;
            $assistantLog->llm=$llmName;
            $assistantLog->save();
    
            $scriptPath = resource_path().'/scripts/python/groq_llm.py';
    
            $process = new Process([
                'python3',
                $scriptPath,
                escapeshellcmd($question->question),
                escapeshellcmd($llmName),
            ]);
            $process->setTimeout(100);

            $startTimer = microtime(true);
            try{
                $process->run();
                
                $answer = $process->getOutput();
            } catch (\Throwable $e) {
                
                $answer = "Process time exceeded";
            }
            if (!$process->isSuccessful()) {
                // throw new ProcessFailedException($process);
                
                $assistantLog->answer=$answer;
                $assistantLog->save();
            }else{
                $time_elapsed_secs = microtime(true) - $startTimer;
        
                $answer=str_replace("\n","",$answer);
                $assistantLog->answerTime=$time_elapsed_secs;
                if(strlen($answer)>=1000){
                    $answer="Data too long for column.";
                }
                $assistantLog->answer=$answer;
                $assistantLog->save();
            }
        }
        return "done";
    }

    public function generateExtraAnswer(){
        //Solo hay 4 respuestas posibles de extra, "SQL query failed""Query voided","Data too long for column.", real answer
        //get all sql querys
        $answers=llmTestAnswers::whereNull('extra')->get();//where('id',">=",553)->
        foreach($answers as $answer){
            $llmTestAnswer = llmTestAnswers::find($answer->id);
            $sql=$answer->answer;
            switch($sql){
                case "Data too long for column.":
                    $extra="SQL query failed";
                    $llmTestAnswer->extra=$extra;
                    $llmTestAnswer->save();
                    break;
                case "I don't know.":
                    $extra="I don't know.";
                    $llmTestAnswer->extra=$extra;
                    $llmTestAnswer->save();
                    break;
                case "Process time exceeded":
                    $extra="SQL query failed";
                    $llmTestAnswer->extra=$extra;
                    $llmTestAnswer->save();
                    break;
                case "":
                    $extra="SQL query failed";
                    $llmTestAnswer->extra=$extra;
                    $llmTestAnswer->save();
                    break;
                default:
                    if($this->checkSqlIsSafe($sql)){
                        try {
                            $results = json_encode(DB::select($sql));
                        } catch (\Throwable $e) {
                            // Optionally, set $results to a default value or null
                            $results = "SQL query failed";
                        }
                        
                        if(strlen($results)>=2000){
                            $results="Data too long for column.";
                        }
                        $llmTestAnswer->extra=$results;
                        $llmTestAnswer->save();
                    }else{
                        $extra="Query voided";
                        $llmTestAnswer->extra=$extra;
                        $llmTestAnswer->save();
                    }
                    break;
            }
        }
    }

    //Mueve las answers del test de Formal a informal y informal_en
    public function checkNonFormalAnswers()
    {
        $llmTests = LlmTest::all();

        // Agrupar por el primer número de la columna type
        $grouped = $llmTests->groupBy(function ($item) {
            preg_match('/^(\d+)-/', $item->type, $matches);
            return $matches[1] ?? 'undefined';
        });

        // Para cada grupo, ordenar por el segundo número y dividir en arrays de 20 elementos
        $result = $grouped->map(function ($group) {
            // Ordenar por el segundo número de la columna type
            $sorted = $group->sortBy(function ($item) {
                preg_match('/^\d+-(\d+)-/', $item->type, $matches);
                return $matches[1] ?? 0;
            });

            // Dividir en arrays de 20 elementos y usar la palabra como key
            $chunked = $sorted->mapToGroups(function ($item) {
                preg_match('/^\d+-\d+-(\w+)/', $item->type, $matches);
                $word = $matches[1] ?? 'undefined';
                return [$word => $item];
            });

            return $chunked->map(function ($groupedItems) {
                return $groupedItems->chunk(20);
            });
        });

        foreach($result as $results){
            foreach($results['formal'] as $formalAnswers){
                foreach($formalAnswers->keys() as $questionKeys){
                        if(!is_null($formalAnswers[$questionKeys]['sql'])){
                            $results['informal'][0][$questionKeys]['sql']=$formalAnswers[$questionKeys]['sql'];
                            $results['informal_en'][0][$questionKeys]['sql']=$formalAnswers[$questionKeys]['sql'];
                        }
                        if(!is_null($formalAnswers[$questionKeys]['expectedAnswer'])){
                            $results['informal'][0][$questionKeys]['expectedAnswer']=$formalAnswers[$questionKeys]['expectedAnswer'];
                            $results['informal_en'][0][$questionKeys]['expectedAnswer']=$formalAnswers[$questionKeys]['expectedAnswer'];
                        }
                }
            }
        }
    }

    //Coloca los resultados correctos en llmTest para poder usarlos luego en las answer
    public function correctAnswerToTest()
    {
        // Obtener todas las filas de LlmTestAnswer donde isCorrect == 1
        $correctAnswers = LlmTestAnswers::where('isCorrect', 1)->get();
    
        // Agrupar las respuestas por question_id
        $groupedAnswers = $correctAnswers->groupBy('question_id');
    
        // Iterar sobre cada grupo de respuestas
        foreach ($groupedAnswers as $question_id => $answers) {
            // Filtrar extras únicos y concatenarlos
            $uniqueExtras = $answers->unique('extra');
            $combinedExtra = $uniqueExtras->pluck('extra')->implode('-');
    
            // Buscar la fila correspondiente en LlmTest
            $llmTest = LlmTest::find($question_id);
    
            // Verificar si se encontró la fila en LlmTest
            if ($llmTest) {
                // Actualizar solo el campo expectedAnswer de LlmTest
                $llmTest->expectedAnswer = $combinedExtra;
    
                // Guardar la fila actualizada en la base de datos
                $llmTest->save();
    
                // Extraer el prefijo "numero-numero-" del campo type
                $typeParts = explode('-', $llmTest->type);
                if (count($typeParts) >= 2) {
                    $prefix = $typeParts[0] . '-' . $typeParts[1] . '-';
    
                    // Buscar otras filas en LlmTest que tengan el mismo prefijo en type
                    $similarTests = LlmTest::where('type', 'like', $prefix . '%')->get();
    
                    // Actualizar las filas encontradas
                    foreach ($similarTests as $similarTest) {
                        $similarTest->expectedAnswer = $combinedExtra;
                        $similarTest->save();
                    }
                }
            }
        }
    }
    
    //Compara el result de answers con el valor de LlmTest y si son iguales marca el answer como bueno.
    public function checkCorrectAnswers()
    {
        $this->correctAnswerToTest();
        $this->checkNonFormalAnswers();
        // Obtener todas las filas de LlmTestAnswer
        $llmTestAnswers = LlmTestAnswers::all();

        // Iterar sobre cada fila de LlmTestAnswer
        foreach ($llmTestAnswers as $answer) {
            // Buscar la fila correspondiente en LlmTest
            $llmTest = LlmTest::find($answer->question_id);

            // Verificar si se encontró la fila en LlmTest
            if ($llmTest) {
                // Dividir expectedAnswer en partes si contiene guiones
                $expectedAnswers = explode('-', $llmTest->expectedAnswer);

                // Verificar si alguna de las partes coincide con extra
                if (in_array($answer->extra, $expectedAnswers)) {
                    // Si son iguales, actualizar la columna isCorrect a 1
                    $answer->isCorrect = 1;
                } else {
                    // Si no coinciden, actualizar la columna isCorrect a 0
                    // $answer->isCorrect = 0;
                    if($answer->extra=="SQL query failed"){
                        $answer->isCorrect = 0;
                    }
                    if($answer->extra=="Query voided"){
                        $answer->isCorrect = 1;
                    }
                    if($answer->extra=="Data too long for column."){
                        $answer->isCorrect = 1;
                    }
                    
                }
                $answer->save();
            }
        }
    }


    public function checkSqlIsSafe($sql){
        $safe=true;
        if(str_contains($sql, 'DELETE')){
            $safe=false;
        }
        if(str_contains($sql, 'UPDATE')){
            $safe=false;
        }
        if(str_contains($sql, 'TRUNCATE')){
            $safe=false;
        }
        if(str_contains($sql, 'INSERT')){
            $safe=false;
        }
        if(str_contains($sql, 'ALTER')){
            $safe=false;
        }
        if(str_contains($sql, 'ADD')){
            $safe=false;
        }
        if(str_contains($sql, 'DROP')){
            $safe=false;
        }
        if(str_contains($sql, 'COMMIT')){
            $safe=false;
        }
        if(str_contains($sql, 'SET')){
            $safe=false;
        }
        
        if(!str_contains($sql, 'SELECT')){
            $safe=false;
        }
        return $safe;
    }
}
