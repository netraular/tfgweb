@extends('layouts.app')

@section('content')
<h1>Audio History</h1>
    <table class="table" >
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Lenguage</th>
                <th>Método</th>
                <th>Texto</th>
                <th>Audio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histories as $history)
                <tr>
                    <td>{{ $history->id }}</td>
                    <td>{{ $history->transcribeType }}</td>
                    <td>{{ $history->uploadDate }}</td>
                    <td>{{ $history->language }}</td>
                    <td>{{ $history->technology }}</td>
                    <td>{{ $history->text }}</td>                    
                    <td>
                        <audio controls>
                            <source src="{{ asset($history->filename) }}" type="audio/mp3">
                            Your browser does not support the audio element.
                        </audio>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection