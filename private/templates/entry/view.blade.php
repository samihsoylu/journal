@extends('base')

@isset($entry)
    @section('pageTitle', $entry->getTitle())
@endisset

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                @isset($entry)
                    <p>{!! $entry->getContentAsMarkup() !!}</p>
                @endisset
            </div>
            <div>
                <button class="btn btn-default" type="button" onclick="window.location.href='{{ $entries_url }}';"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                @isset($entry)
                <button class="btn btn-primary" type="button" onclick="window.location.href='{{ $entry_url }}/{{ $entry->getId() }}/update';"><i class="material-icons">edit</i> Edit</button>
                @endisset
            </div>
            <br />
        </div>
    </div>
@endsection