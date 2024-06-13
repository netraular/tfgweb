@extends('layouts.app')

@section('title', 'Comparación de Preguntas')

@section('content')
    @if(isset($message))
        <div class="alert alert-info mt-5">{{ $message }}</div>
    @else
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Respuesta ID: 
                        </div>
                        <div>
                            <select class="form-select" name="selected_answer_id" id="selected_answer_id">
                                @foreach($allAnswersIds as $id)
                                    <option value="{{ $id }}" @if($id == $answer->id) selected @endif>{{ $id }}</option>
                                @endforeach
                            </select>
                            <script>
                                // Obtener el elemento select
                                const selectAnswerId = document.getElementById('selected_answer_id');

                                // Manejar el evento change del elemento select
                                selectAnswerId.addEventListener('change', function() {
                                    // Obtener el valor seleccionado
                                    const selectedId = this.value;

                                    // Construir la URL con el nuevo ID seleccionado
                                    const url = "{{ route('showComparisons') }}?selected_answer_id=" + selectedId;

                                    // Redirigir a la nueva URL
                                    window.location.href = url;
                                });
                            </script>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                            Pregunta ID:[ <b>{{$question->id}}</b> ] Tipo: [<b>{{$question->type}}</b>] Llm: [<b>{{$answer->llm}}</b>]
                    </div>
                </div>
                <div class="form-check">
                    <input form="updateAnswerForm" type="checkbox" class="form-check-input" id="showAnswered" name="showAnswered" @if(request()->has('showAnswered')) checked @endif>
                    <label class="form-check-label" for="showAnswered">Mostrar respondidas</label>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $question->question }}</h5>
                <p class="card-text">Respuesta: {{ $answer->answer }}</p>

                @if(isJson($answer->extra))
                    @php
                        $extras = json_decode($answer->extra, true);
                    @endphp
                    @if(empty($extras))
                        <p class="card-text">Extra: No hay datos adicionales.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    @foreach(array_keys($extras[0]) as $header)
                                        <th>{{ ucfirst($header) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($extras as $extra)
                                    <tr>
                                        @foreach($extra as $value)
                                            <td>{{ $value }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @else
                    <p class="card-text">Extra: {{ $answer->extra }}</p>
                @endif

                <form id="updateAnswerForm" action="{{ route('showComparisons') }}" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="answer_id" value="{{ $answer->id }}">

                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" name="isCorrect" value="1" class="btn btn-success">Correcto</button>
                            <button type="submit" name="isCorrect" value="0.5" class="btn btn-warning">No del todo</button>
                            <button type="submit" name="isCorrect" value="0" class="btn btn-danger">Incorrecto</button>
                        </div>
                        <div>
                            <button type="submit" name="isCorrect" value='2' class="btn btn-secondary">Saltar pregunta</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@php
function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
@endphp