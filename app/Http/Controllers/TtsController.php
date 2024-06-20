<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Models\audioHistory;
use Illuminate\Support\Facades\Storage;

class TtsController extends Controller
{
    public function textToSpeechApi(Request $request){
        $texto = $request->input('texto');
        $nombreArchivoAudio = date("YmdHms");
        $process = new Process(['python3',resource_path().'/scripts/python/googleTts.py',escapeshellcmd($texto),escapeshellcmd($nombreArchivoAudio)]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $pathToFile = storage_path("app/public/googleTTS/$nombreArchivoAudio.mp3");
        $path = Storage::disk('public')->putFileAs('googleTTS/', $pathToFile, 'test.mp3');
        // $path = Storage::disk('public')->put('audios/tts/googleTts', $fileContent);
        // $path = $fileContent->storeAs('audios/tts/googleTts', $fileContent,'public');


        $audioLog = new audioHistory;
        $audioLog->transcribeType="TTS";
        $audioLog->filename="/storage/googleTTS/$nombreArchivoAudio.mp3";
        $audioLog->language=app()->getLocale();
        $audioLog->technology="google API";
        $audioLog->text=$texto;
        $audioLog->save();
        return response()->download($pathToFile);
    }

    public function textToSpeechLocal(Request $request){
        $texto = $request->input('texto');
        $nombreArchivoAudio = date("tts-YmdHis");
        $idioma='es';

        $scriptPath = resource_path().'/scripts/python/localTts.py';


        $process = new Process([
            'sudo',
            'python3',
            $scriptPath,
            escapeshellcmd($texto),
            escapeshellcmd($nombreArchivoAudio),
            $idioma
        ]);
        

        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = $process->getOutput();

        $pathToFile = storage_path("app/public/localTTS/$nombreArchivoAudio.mp3");
        return response()->download($pathToFile);
    }
    
}
