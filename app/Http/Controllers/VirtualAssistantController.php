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
use Log;
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
                    $results = "Error.";
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
                        $llmTestAnswer->extra="$extra";
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


    public function checkSqlIsSafe(string $sql): bool
    {
        // 1. Normalizar y limpiar un poco el SQL (quitar espacios extra al inicio/fin)
        $trimmedSql = trim($sql);
        $sqlLower = strtolower($trimmedSql);

        // 2. Asegurarse de que EMPIEZA con SELECT (ignorando espacios iniciales)
        // Usamos una expresión regular para ser un poco más robustos que solo strpos
        // ^\s* : inicio de línea seguido de cero o más espacios
        // SELECT : la palabra clave SELECT
        // \s+ : uno o más espacios después de SELECT
        // i : case-insensitive
        if (!preg_match('/^\s*SELECT\s+/i', $trimmedSql)) {
            Log::debug("SQL no empieza con SELECT: " . $sql);
            return false;
        }

        // 3. Buscar palabras clave absolutamente prohibidas (case-insensitive)
        $forbiddenKeywords = [
            'delete', 'update', 'truncate', 'insert', 'alter', 'add', 'drop',
            'commit', 'rollback', 'set', 'create', 'grant', 'revoke', 'use',
            'exec', 'execute', 'information_schema', 'schema_name',
            'pg_sleep', 'sleep', 'benchmark', // Funciones peligrosas específicas de BD
            '--',  '/*', '*/' // Intentos de comentar o terminar la consulta prematuramente (básico)
        ];

        foreach ($forbiddenKeywords as $keyword) {
            // Usamos \b para buscar palabras completas y evitar falsos positivos (ej: 'update' en 'nonupdateable')
            // aunque esto no es perfecto contra ofuscación.
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $sqlLower)) {
                Log::debug("Palabra clave prohibida '$keyword' encontrada en SQL: " . $sql);
                return false;
            }
            // Comprobación simple adicional por si \b falla con caracteres especiales
             if (str_contains($sqlLower, $keyword)) {
                 // Revisión adicional menos precisa pero más amplia
                 // Cuidado con falsos positivos (ej: 'information_schema' en un string literal)
                 // Para este caso específico, si la palabra clave está en cualquier lugar, lo bloqueamos.
                 Log::debug("Subcadena prohibida '$keyword' encontrada en SQL: " . $sql);
                 return false;
             }
        }

        // 4. Extraer nombres de tablas después de FROM y JOIN
        // Esta regex es SIMPLIFICADA. No manejará correctamente todos los casos de SQL complejo
        // (subconsultas en FROM/JOIN, alias complejos, comentarios entre palabras clave, etc.)
        // \b(?:FROM|JOIN)\s+ : Busca la palabra FROM o JOIN seguida de espacios
        // (?:[\w`]+\.)? : Opcionalmente captura un prefijo de esquema/base de datos (ej: `db`. o schema.) - no lo guardamos
        // ([\w`]+) : Captura el nombre de la tabla (letras, números, _, `) - ESTO ES LO QUE QUEREMOS
        // \b : Límite de palabra
        // i : Case-insensitive
        preg_match_all('/\b(?:FROM|JOIN)\s+(?:[\w`]+\.)?([\w`]+)\b/i', $trimmedSql, $matches);

        if (empty($matches[1])) {
            // Si es un SELECT pero no encontramos tablas después de FROM/JOIN, es sospechoso o inválido
            Log::debug("No se pudieron extraer tablas de FROM/JOIN en SQL: " . $sql);
            return false;
        }

        $extractedTables = array_map('strtolower', array_unique($matches[1])); // Nombres de tabla únicos en minúsculas

        // 5. Verificar si TODAS las tablas extraídas están en la lista permitida
        $allowedTables = ['material', 'proyecto', 'trabajador', 'trabajadoresdelproyecto']; // Lista permitida en minúsculas

        foreach ($extractedTables as $table) {
             // Quitamos backticks si los hubiera (común en MySQL)
             $tableNameClean = trim($table, '`');
            if (!in_array($tableNameClean, $allowedTables)) {
                Log::debug("Tabla no permitida '$tableNameClean' encontrada en SQL: " . $sql);
                return false; // Encontramos una tabla no permitida
            }
        }

        // 6. Si pasó todas las verificaciones, asumimos que es "segura" según nuestras reglas limitadas
        return true;
    }
}
