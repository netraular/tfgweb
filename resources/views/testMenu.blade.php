
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
   
    <hr>

    <div class="row">
    <div class="col-md-auto">
        <button onclick="showIDS()" > Tipo 1</button>
        <button onclick="showTest('type2test','type1test')" > Tipo 2</button>

        <br><br>

        <form method="POST" action="{{url('/')}}" id="type1test" class="form-inline" style="display:block">
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

        <form method="POST" action="{{url('/')}}" id="type2test" class="form-inline" style="display:none">
            {{ csrf_field() }}
            <input type="hidden" name="questionType" value="2">

            <div style="display:inline-block">
                De la tabla 
                <select required  id="Type2Selector1" name="inputText1" class="form-select-sm" >
                    <option selected="true" disabled="disabled"></option>    
                    @foreach(array_keys($preguntas[1]) as $tabla)
                    <option value="{{$tabla}}">{{$tabla}}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:inline-block">
                @foreach(array_keys($preguntas[1]) as $tabla)
                    <div style="display:none">
                        dame la columna 
                        <select onchange="showThirdOption(this)" id="Type2Selector2_{{$tabla}}" name="inputText2_{{$tabla}}" class="form-select-sm" >
                            <option selected="true" disabled="disabled"></option>    
                            @foreach($preguntas[1][$tabla] as $column)
                            <option value="{{$column}}">{{$column}} </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <div style="display:inline-block">
                @foreach(array_keys($preguntas[1]) as $tabla)
                    <div style="display:none">
                        donde el valor
                        <select id="Type2Selector3_{{$tabla}}" name="inputText3_{{$tabla}}" class="form-select-sm" >
                            <option selected="true" disabled="disabled"></option>    
                            @foreach($preguntas[1][$tabla] as $column)
                            <option value="{{$column}}">{{$column}} </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <div id="inputText3_submit" style="display:none">
                    =
                    <input class="form-control-sm" type="text" id="valueEqual" name="valueEqual">
                    <button  type="submit" class="btn btn-primary" >Submit</button>
                </div>
            </div>

        </form>


    </div>
    </div>
    
    @if(isset($inputText))
    <div class="container">
        <hr>
        <p>P: {!! $inputText !!}</p>
        <p>R: @php dump($output['respuesta']); @endphp</p>
    </div>
    @endif
</div>
</div>

@endsection



<script>
    function showIDS(){
        var elements = document.querySelectorAll('[id^="testType1"]');
        console.log(elements)
        // Itera sobre los elementos seleccionados y haz algo con cada uno
        elements.forEach(function(element) {
            console.log(element.id); // Imprime el ID de cada elemento
        });

    }

//Question type selector
 function showTest(main,others){
    document.getElementById(main).style.display = 'block';
    // hide the lorem ipsum text
    document.getElementById(others).style.display = 'none';
 }

 //Type 2 hide or show options
 window.addEventListener('load', function () {

    document.getElementById('Type2Selector1').addEventListener('change', function() {
        document.getElementById("Type2Selector2_"+this.value).parentElement.style.display = "inline-block";

        switch("Type2Selector2_"+this.value){
            case "Type2Selector2_"+"trabajador":
                document.getElementById("Type2Selector2_"+"proyecto").parentElement.style.display = "none";
                document.getElementById("Type2Selector2_"+"material").parentElement.style.display = "none";
                break;
            case "Type2Selector2_"+"proyecto":
                document.getElementById("Type2Selector2_"+"trabajador").parentElement.style.display = "none";
                document.getElementById("Type2Selector2_"+"material").parentElement.style.display = "none";
                break;
            case "Type2Selector2_"+"material":
                document.getElementById("Type2Selector2_"+"proyecto").parentElement.style.display = "none";
                document.getElementById("Type2Selector2_"+"proyecto").parentElement.style.display = "none";
                break;
        }
    
    });
 });

function showThirdOption(secondElement){
    tabla=secondElement.id.split('_')[1];
    document.getElementById("Type2Selector3_"+tabla).parentElement.style.display = "inline-block";

    switch("Type2Selector3_"+tabla){
        case "Type2Selector3_"+"trabajador":
            document.getElementById("Type2Selector3_"+"proyecto").parentElement.style.display = "none";
            document.getElementById("Type2Selector3_"+"material").parentElement.style.display = "none";
            break;
        case "Type2Selector3_"+"proyecto":
            document.getElementById("Type2Selector3_"+"trabajador").parentElement.style.display = "none";
            document.getElementById("Type2Selector3_"+"material").parentElement.style.display = "none";
            break;
        case "Type2Selector3_"+"material":
            document.getElementById("Type2Selector3_"+"proyecto").parentElement.style.display = "none";
            document.getElementById("Type2Selector3_"+"proyecto").parentElement.style.display = "none";
            break;
    }
    document.getElementById("inputText3_submit").style.display="inline-block";


}

</script>