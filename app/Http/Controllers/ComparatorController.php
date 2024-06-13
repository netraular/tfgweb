<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\llmTest;
use App\Models\llmTestAnswers;

class ComparatorController extends Controller
{
    public function showComparisons(Request $request)
    {
        // Verificar si el request contiene los datos necesarios para actualizar una respuesta
        if ($request->filled(['answer_id', 'isCorrect'])) {
            $this->updateAnswer($request);
        }

        // Determinar si se debe mostrar respondidas
        $showAnswered = $request->has('showAnswered');

        $answer = $this->getNextAnswer($request);

        if ($answer) {
            $question = LlmTest::find($answer->question_id);

            // Obtener los IDs de todas las respuestas disponibles
            $allAnswersIds = LlmTestAnswers::orderBy('id')->pluck('id');

            return view('comparisons.showComparisons', compact('question', 'answer','showAnswered','allAnswersIds'));
        } else {
            return view('comparisons.showComparisons')->with('message', 'No hay preguntas pendientes de revisar.');
        }
    }
    private function getNextAnswer(Request $request)
    {
        if ($id = $request->input('selected_answer_id')) {
            $answer = LlmTestAnswers::find($id);
        } else{    
            $onlyFormalQuestions=true;  //Solo usamos Formal questions para evaluar.
            $showAnswered = $request->has('showAnswered');    
            
            if($request->filled(['answer_id'])){
                //Si tenemos answer_id mostramos el siguiente answer_id
                $answer = LlmTestAnswers::where('id', '>', $request->answer_id);
                if (!$showAnswered){$answer=$answer->whereNull('isCorrect');}    //Mostramos solo las no clasificadas
                if($onlyFormalQuestions){$answer=$this->formalAnswer($answer);}
                $answer=$answer->orderBy('id')->first();
            }else{
                //Si NO tenemos asnwer_id mostramos el primer answer_id
                $answer = new LlmTestAnswers;
                if (!$showAnswered){$answer=$answer->whereNull('isCorrect');}
                if($onlyFormalQuestions){$answer=$this->formalAnswer($answer);}
                $answer=$answer->orderBy('id')->first();
            }
        }
        return $answer;
    }
    
    public function updateAnswer(Request $request)
    {
        $answer = LlmTestAnswers::find($request->answer_id);
        $answer->isCorrect = $request->isCorrect;
        $answer->save();
    }

    private function formalAnswer($answer){
        $answer=$answer->whereHas('question', function ($query) {
            $query->where('type', 'LIKE', '%-formal');
        });
        return $answer;
    }
}
