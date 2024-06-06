<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Models\audioHistory;

class SttController extends Controller
{
    public function speechToTextApi(Request $request){
        if ($request->hasFile('audio') && $request->file('audio')->isValid()) {
            // Obtener el archivo de audio del request
            $audioFile = $request->file('audio');

            // Generar un nombre único para el archivo
            $filename =  date("YmdHms").'_' .$audioFile->getClientOriginalName();

            // Guardar el archivo en el disco 'public' en una carpeta 'audios'
            $path = $audioFile->storeAs('googleSTT', $filename,'public');

            //Llamar a la api stt de google con el audio
            $process = new Process(['python3',resource_path().'/scripts/python/googleStt.py','app/public/'.escapeshellcmd($path)]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $output = $process->getOutput();

            $audioLog = new audioHistory;
            $audioLog->transcribeType="STT";
            $audioLog->filename='public/'.$path;
            $audioLog->language=app()->getLocale();
            $audioLog->technology="google API";
            $audioLog->text=$output;
            $audioLog->save();

            // Devolver una respuesta con la palabra "ok"
            return response()->json($output);
        }
        return response()->json('error', 400);
    }
}
