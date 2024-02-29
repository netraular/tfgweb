<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simulacion;

class VirtualAssistantController extends Controller
{
    //
    public function testMenu(Request $request){
        $preguntas=['1','dos','3tres'];
        $preguntas=Simulacion::select('pregunta')->get()->toArray();
        if(isset($request->inputText)){
            $inputText=$request->inputText;
            $output=Simulacion::select('respuesta')->where('pregunta','=',$inputText)->first();
            return view('testMenu',compact('preguntas','inputText','output'));
        }else{
            return view('testMenu',compact('preguntas'));
        }
    }
}
