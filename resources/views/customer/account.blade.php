@extends('layouts.customer')

@section('content')

<h2>My Account</h2>

<p>Name: {{ auth()->user()->name }}</p>
<p>Email: {{ auth()->user()->email }}</p>

@endsection