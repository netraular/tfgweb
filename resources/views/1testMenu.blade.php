
@extends('layouts.app')

@section('content')
<div class="container">
<div class="album py-5 bg-light">
    <div class="container">
   
    Esta es la web del tfg de Raúl Aquilué. Puede que la web que estés buscando sea esta? <a href="http://eco2.netshiba.com"> hack4good </a>
    <hr>

    <div class="row">
    <div class="col-md-auto">
        <button onclick="showFunctionalityWithId('functionalityType1')" > Tipo 1</button>
        <button onclick="showFunctionalityWithId('functionalityType2')" > Tipo 2</button>
        <button onclick="showFunctionalityWithId('functionalityType3')" > TTS WebSpeech</button>
        <button onclick="showFunctionalityWithId('functionalityType4')" > STT WebSpeech</button>
        <button onclick="showFunctionalityWithId('functionalityType5')" > TTS API</button>
        <button onclick="showFunctionalityWithId('functionalityType6')" > STT API</button>
        <button onclick="showFunctionalityWithId('functionalityType7')" > local TTS</button>
        <button onclick="showFunctionalityWithId('functionalityType8')" > local STT</button>

        <br><br>
        <div id="functionalityType1" style="display:none">    @include('sections.functionalityType1')</div>
        <div id="functionalityType2" style="display:none">    @include('sections.functionalityType2')</div>
        <div id="functionalityType3" style="display:none">    @include('sections.ttsWebSpeech')</div>
        <div id="functionalityType4" style="display:none">    @include('sections.sttWebSpeech')</div>
        <div id="functionalityType5" style="display:none">    @include('sections.ttsGoogleApi')</div>
        <div id="functionalityType6" style="display:none">    @include('sections.sttGoogleApi')</div>
        <div id="functionalityType7" style="display:none">    @include('sections.localTts')</div>
        <div id="functionalityType8" style="display:none">    @include('sections.localStt')</div>

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



<nav class=" d-flex flex-nowrap navbar navbar-dark p-3 bg-dark box-shadow"> 
                <div class="d-flex flex-row">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto">

                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto">
                            <!-- Authentication Links -->
                            @guest
                                @if (Route::has('login'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                    </li>
                                @endif

                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                @endif
                            @else
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>