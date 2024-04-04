<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TtsController extends Controller
{
    //
    public function generarAudioApi(Request $request){
        $texto = $request->input('texto');
        $nombreArchivoAudio = 'audio_generado';
        $script=resource_path() . "/scripts/python/googleTts.py '{$texto}' '{$nombreArchivoAudio}'";
        $comando = escapeshellcmd("python3 $script");
        $process = new Process(explode(' ', $comando));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        dd($output);
        return response()->json(['archivo' => $nombreArchivoAudio.'mp3']);

        // $pathToFile = storage_path('audios/tts/output.mp3');
        // return response()->download($pathToFile);
    }
}
