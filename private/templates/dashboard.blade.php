@extends('base')

@section('pageTitle', 'Dashboard')

@include('components/header')

@section('content')
    <div class="container-lg clearfix">
        <div class="col-4 float-left border p-4">
            My column
        </div>
        <div class="col-4 float-left border p-4">
            Looks better
        </div>
        <div class="col-4 float-left border p-4">
            Than your column
        </div>
    </div>
@endsection