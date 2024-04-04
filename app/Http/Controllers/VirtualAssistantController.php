<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simulacion;
use App\Models\Trabajador;
use App\Models\Proyecto;
use App\Models\Material;

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
}
