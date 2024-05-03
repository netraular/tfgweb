<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TtsController extends Controller
{
    public function textToSpeechApi(Request $request){
        $texto = $request->input('texto');
        $nombreArchivoAudio = 'audio_generado';
        $process = new Process(['python3',resource_path().'/scripts/python/googleTts.py',escapeshellcmd($texto),escapeshellcmd($nombreArchivoAudio)]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $pathToFile = storage_path("audios/tts/$nombreArchivoAudio.mp3");
        return response()->download($pathToFile);
    }

    public function textToSpeechLocal(Request $request){
        $texto = $request->input('texto');
        $nombreArchivoAudio = 'audio_generado';
        $process=new Process(["echo","ola"]);
        $result = $process->run();
        dd($result->output());
        
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        dd('a');
        $process = new Process(['python3',resource_path().'/scripts/python/localTts.py',escapeshellcmd($texto),escapeshellcmd($nombreArchivoAudio)]);
        dump('python3',resource_path().'/scripts/python/localTts.py',escapeshellcmd($texto),escapeshellcmd($nombreArchivoAudio));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $pathToFile = storage_path("audios/tts/$nombreArchivoAudio.mp3");
        return response()->download($pathToFile);
    }
    
}
