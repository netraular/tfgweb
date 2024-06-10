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
        $assistantLog->answer=$answer;
        $assistantLog->save();
        
        
        if($answer!="I don't know."){
            $sql=$answer;
            if($this->checkSqlIsSafe($sql)){
                try {
                    $results = json_encode(DB::select($sql));
                } catch (\Exception $e) {
                    // Handle the error
                    Log::error('SQL query failed: ' . $e->getMessage());
            
                    // Optionally, set $results to a default value or null
                    $results = "SQL query failed";
                }
                $assistantLog->results=$results;
                $assistantLog->save();
            }else{
                $results="Query voided";
                $assistantLog->results=$results;
                $assistantLog->save();
            }
        }

        return response()->json($answer);
    }
    public function testLlm($llmName){

        $questions=llmTest::all();
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
            $process->setTimeout(3600);

            $startTimer = microtime(true);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $time_elapsed_secs = microtime(true) - $startTimer;
    
            $answer = $process->getOutput();
    
            $answer=str_replace("\n","",$answer);
            $assistantLog->answerTime=$time_elapsed_secs;
            $assistantLog->answer=$answer;
            $assistantLog->save();
            
            
            if($answer!="I don't know."){
                $sql=$answer;
                if($this->checkSqlIsSafe($sql)){
                    $results=json_encode(DB::select($sql));
                    $assistantLog->extra=$results;
                    $assistantLog->save();
                }else{
                    $results="Query voided";
                    $assistantLog->results=$results;
                    $assistantLog->save();
                }
            }
        }
        

        return "done";
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
