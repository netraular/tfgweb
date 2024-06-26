@extends('layouts.app')

@section('content')
    




<h2>Assistant history</h2>


<table style="width:100%" class="table table-hover">
@foreach($histories as $history)
  <thead class="thead-dark">
    <tr >
      <th style="width: 5%;">{{$history['id']}}</th>
      <th>{{$history['question']}}</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="width: 5%;"></td>
      <td>{{$history['answer']}}</td>
    </tr>
    <tr>
      <td></td>
      <td>
        @if(!is_null($history['results']) and $history['results']!="Query voided" and $history['results']!="[]" and $history['results']!="Data too long for column." and $history['results']!="I don't know." and $history['results']!="Process time exceeded")
          <table class="table-bordered">
            <tr>
              @if(isset(json_decode($history['results'])[0]))
                @foreach(array_keys((array)json_decode($history['results'])[0]) as $key)
                  <th>{{$key}}</th>
                @endforeach
              @else
              <th></th>
              @endif
            </tr>
            @if(!is_string(json_decode($history['results'])))
            @foreach(json_decode($history['results']) as $row)
            <tr>
              @foreach(array_keys((array)$row) as $col)
              <td>{{$row->$col}}</td>
              @endforeach
            </tr>
            @endforeach
            @endif
          </table>
        @elseif(!is_null($history['results']))
          {{$history['results']}}
        @endif
      </td>
    </tr>
  </tbody>
  <tr style="outline: thin solid">

@endforeach
</table>

    @endsection

    