@extends('base')

@isset($entry)
    @section('pageTitle', $entry->getTitle())
@endisset

@section('content')
    @include('components/header')
    @isset($entry)
    <div class="container">
        <div class="row row-align">
            <div class="col s12 m10 l7 offset-m1 offset-l2 entry-content">
                <p>{!! $entry->getContent() !!}</p>
            </div>
        </div>
        <div class="row row-align">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <p class="small-text">Category: {{ $entry->getCategoryName() }}, Last updated: {{ $entry->getLastUpdatedTimestamp($session->getTimezone()) }}</p>
                <!-- javascript in the back button ensures that the filters are not lost when you go back to the entries page -->
                <button class="btn btn-default" type="button" onclick="@if(isset($success)) window.location.href='{{ $entries_url }}' @else window.history.go(-1); return false; @endif"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                <button class="btn btn-primary" type="button" onclick="window.location.href='{{ $entry_url }}/{{ $entry->getId() }}/update';"><i class="material-icons">edit</i> Edit</button>
            </div>
        </div>
    </div>
    @endisset
@endsection