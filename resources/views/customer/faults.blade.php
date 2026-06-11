@extends('layouts.customer')

@section('content')

<h2>My Reported Faults</h2>

@foreach($faults as $fault)
<div>
    <p>{{ $fault->type }} - {{ $fault->status }}</p>
</div>
@endforeach

@endsection