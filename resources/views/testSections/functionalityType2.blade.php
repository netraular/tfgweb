<form method="POST" action="{{url('/testMenu')}}" class="form-inline">
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

@if(isset($inputText))
    <hr>
    <p>P: {!! $inputText !!}</p>
    <p>R: @php dump($output['respuesta']); @endphp</p>
@endif

<script>

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