@extends('layouts.app')

@section('content')
<div class="container">
<div class="album py-5 bg-light">
    <div class="container">

    <p>Objetivos del menú: </p>
    <ol>
    <li>Digo en voz una pregunta, me da por voz una respuesta </li>
    <li>Selecciono en el menú una pregunta, me da respuesta </li>
    </ol>

    <div class="row">
        <div class="col-md-auto">
            <form method="POST" action="{{url('/')}}" class="form-inline">
            {{ csrf_field() }}
                <div class="form-group mb-2">
                    <label for="exampleInputPassword1">Input</label>
                    <select name="inputText" class="form-select" aria-label="Default select example">
                        @foreach($preguntas as $pregunta)
                        <option value="{{$pregunta['pregunta']}}">{{$pregunta['pregunta']}}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
    </div>
    @if(isset($inputText))
    <div class="container">
        <hr>
        <p>P: {{$inputText}}</p>
        <p>R: {{$output['respuesta']}}</p>
    </div>
    @endif
</div>
</div>
@endsection