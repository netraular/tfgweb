
@extends('layouts.app')

@section('content')
<div class="container">
<div class="album py-5 bg-light">
    <div class="container">
   
    Pruebas de los diferentes componentes de la web. Consultas en local esperadas, transcripción de texto a audio y audio a texto tanto en el cliente, como en el servidor local como mediante api.
    <hr>

    <div class="row">
    <div class="col-md-auto">
        <button onclick="showFunctionalityWithId('functionalityType1')" > consulta db 1</button>
        <button onclick="showFunctionalityWithId('functionalityType2')" > consulta db 2</button>
        <button onclick="showFunctionalityWithId('functionalityType3')" > TTS WebSpeech</button>
        <button onclick="showFunctionalityWithId('functionalityType4')" > STT WebSpeech</button>
        <button onclick="showFunctionalityWithId('functionalityType5')" > TTS API</button>
        <button onclick="showFunctionalityWithId('functionalityType6')" > STT API</button>
        <button onclick="showFunctionalityWithId('functionalityType7')" > local TTS</button>
        <button onclick="showFunctionalityWithId('functionalityType8')" > local STT</button>

        <br><br>
        <div id="functionalityType1" style="display:none">    @include('testSections.functionalityType1')</div>
        <div id="functionalityType2" style="display:none">    @include('testSections.functionalityType2')</div>
        <div id="functionalityType3" style="display:none">    @include('testSections.ttsWebSpeech')</div>
        <div id="functionalityType4" style="display:none">    @include('testSections.sttWebSpeech')</div>
        <div id="functionalityType5" style="display:none">    @include('testSections.ttsGoogleApi')</div>
        <div id="functionalityType6" style="display:none">    @include('testSections.sttGoogleApi')</div>
        <div id="functionalityType7" style="display:none">    @include('testSections.localTts')</div>
        <div id="functionalityType8" style="display:none">    @include('testSections.localStt')</div>

    </div>
    </div>
    

</div>
</div>

@endsection



<script>

//Muestra el div test de el tipo seleccionado y oculta el resto
function showFunctionalityWithId(functionalityTypeID){
    var elements = document.querySelectorAll('[id^="functionalityType"]');
    elements.forEach(function(element) {
        if(element.id==functionalityTypeID){
            element.style.display = 'block';
        }else{
            element.style.display = 'none';
        }
    });
}

</script>
