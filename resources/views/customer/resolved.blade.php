@extends('layouts.customer')

@section('content')

<h2>Resolved Faults</h2>

@foreach($faults as $fault)
<div>
    <p>{{ $fault->type }} - {{ $fault->status }}</p>
</div>
@endforeach

@endsection