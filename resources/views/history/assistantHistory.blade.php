@extends('layouts.app')

@section('content')
<h2>Assistant history</h2>

<table style="width:100%" class="table table-hover">
@foreach($histories as $history)
  <thead class="thead-dark">
    <tr>
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
        @if(!empty($history['results']) && 
            $history['results'] != "Query voided" && 
            $history['results'] != "[]" && 
            $history['results'] != "Data too long for column." && 
            $history['results'] != "I don't know." && 
            $history['results'] != "Process time exceeded")
          @php
            $decodedResults = json_decode($history['results']);
          @endphp
          
          @if(is_array($decodedResults) || is_object($decodedResults))
            <table class="table-bordered">
              @if(isset($decodedResults[0]))
                <tr>
                  @foreach(array_keys((array)$decodedResults[0]) as $key)
                    <th>{{$key}}</th>
                  @endforeach
                </tr>
              @else
                <tr><th></th></tr>
              @endif
              
              @foreach($decodedResults as $row)
                @if(is_object($row) || is_array($row))
                <tr>
                  @foreach(array_keys((array)$row) as $col)
                    <td>{{ is_object($row) ? $row->$col : $row[$col] }}</td>
                  @endforeach
                </tr>
                @endif
              @endforeach
            </table>
          @else
            {{ $history['results'] }}
          @endif
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