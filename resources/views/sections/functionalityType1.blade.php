<form method="POST" action="{{url('/testMenu')}}" class="form-inline" >
    {{ csrf_field() }}
    <input type="hidden" name="questionType" value="1">
    <div class="form-group mb-2">
        <select name="inputText1" class="form-select">
            @foreach($preguntas[0] as $pregunta)
            <option value="{{$pregunta['pregunta']}}">{{$pregunta['pregunta']}}</option>
            @endforeach
        </select>

    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

@if(isset($inputText))
    <hr>
    <p>P: {!! $inputText !!}</p>
    <p>R: @php dump($output['respuesta']); @endphp</p>
@endif