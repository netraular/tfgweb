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

    public function main(Request $request){

        $texto = $request->texto;

        $assistantLog = new assistantHistory;
        $assistantLog->question=$request->texto;
        $assistantLog->save();

        $scriptPath = resource_path().'/scripts/python/llama3api.py';


        $process = new Process([
            'sudo',
            'python3',
            $scriptPath,
            escapeshellcmd($texto),
        ]);
        
        $startTimer = microtime(true);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $time_elapsed_secs = microtime(true) - $startTimer;
        $answer = $process->getOutput();

        $answer=str_replace("\n","",$answer);
        $assistantLog->answerTime=$time_elapsed_secs;
        if(strlen($answer)>=1000){
            $answer="Data too long for column.";
        }
        $assistantLog->answer=$answer;
        $assistantLog->save();
        
        
        if($answer!="I don't know."){
            $sql=$answer;
            if($this->checkSqlIsSafe($sql)){
                try {
                    $results = DB::select($sql);
                } catch (\Throwable $e) {
            
                    // Optionally, set $results to a default value or null
                    $results = "SQL query failed";
                }
                if(strlen(json_encode($results))>=1000){
                    $results="Data too long for column.";
                    $assistantLog->results=$results;
                    $assistantLog->save();
                }else{
                    $assistantLog->results=json_encode($results);
                    $assistantLog->save();
                }
            }else{
                $results="Query voided";
                $assistantLog->extra=$results;
                $assistantLog->save();
            }
        }else{
            $assistantLog->extra=$answer;
            $assistantLog->save();
        }

        return response()->json([$answer,$results]);
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
    
            $scriptPath = resource_path().'/scripts/python/llmApi.py';
    
            $process = new Process([
                'sudo',
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

        // Iterar sobre cada fila de LlmTestAnswer
        foreach ($correctAnswers as $answer) {
            // Buscar la fila correspondiente en LlmTest
            $llmTest = LlmTest::find($answer->question_id);

            // Verificar si se encontró la fila en LlmTest
            if ($llmTest) {
                // Actualizar las columnas de LlmTest
                $llmTest->sql = $answer->answer;
                $llmTest->expectedAnswer = $answer->extra;

                // Guardar la fila actualizada en la base de datos
                $llmTest->save();

                // Extraer el prefijo "numero-numero-" del campo type
                $typeParts = explode('-', $llmTest->type);
                if (count($typeParts) >= 2) {
                    $prefix = $typeParts[0] . '-' . $typeParts[1] . '-';
                    
                    // Buscar otras filas en LlmTest que tengan el mismo prefijo en type
                    $similarTests = LlmTest::where('type', 'like', $prefix . '%')->get();
                    
                    // Aquí puedes realizar cualquier operación con las filas encontradas
                    // Por ejemplo, imprimir las filas encontradas
                    foreach ($similarTests as $similarTest) {
                        $similarTest->sql = $answer->answer;
                        $similarTest->expectedAnswer = $answer->extra;
                        $similarTest->save();
                    }
                }
            }
        }
    }
    
    //Compara el result de answers con el valor de LlmTest y si són iguales marca el answer como bueno.
    public function checkCorrectAnswers()
    {
        // Obtener todas las filas de LlmTestAnswer
        $llmTestAnswers = LlmTestAnswers::all();
    
        // Iterar sobre cada fila de LlmTestAnswer
        foreach ($llmTestAnswers as $answer) {
            // Buscar la fila correspondiente en LlmTest
            $llmTest = LlmTest::find($answer->question_id);
    
            // Verificar si se encontró la fila en LlmTest y comparar expectedAnswer y extra
            if ($llmTest && $llmTest->expectedAnswer === $answer->extra) {
                // Si son iguales, actualizar la columna isCorrect a 1
                $answer->isCorrect = 1;
                $answer->save();
            }elseif($llmTest && is_null($answer->extra)){
                $answer->isCorrect = 0;
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
