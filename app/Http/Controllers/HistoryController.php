<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\audioHistory;
use App\Models\assistantHistory;

class HistoryController extends Controller
{
    //
    public function showAudioHistory(Request $request){
        $histories = AudioHistory::orderBy('id','desc')->get();

        // Retornar la vista 'history' con los datos obtenidos
        return view('history/historyMainView', compact('histories'));
    }

    public function showAssistantHistory(Request $request){
        $histories = AssistantHistory::orderBy('id','desc')->limit(50)->get()->toArray();

        // Retornar la vista 'history' con los datos obtenidos
        return view('history/assistantHistory', compact('histories'));
    }
}
