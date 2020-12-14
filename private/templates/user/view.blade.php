@extends('base')

@isset($user)
    @section('pageTitle', ucfirst($user->getUsername()))
@endisset

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align">
            <div class="col s12 m10 l7 offset-m1 offset-l2 entry-content">

            </div>
        </div>
        <div class="row row-align">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                @isset($user)
                    <!-- Maybe a form with disabled fields? -->

                    <p class="small-text">Last updated: {{ $user->getLastUpdatedTimestampFormatted() }}</p>
                    <!-- javascript in the back button ensures that the filters are not lost when you go back to the entries page -->
                    <button class="btn btn-default" type="button" onclick="@if(isset($success)) window.location.href='{{ $users_url }}' @else window.history.go(-1); return false; @endif"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                    <button class="btn btn-primary" type="button" onclick="window.location.href='{{ $user_url }}/{{ $user->getId() }}/update';"><i class="material-icons">edit</i> Edit</button>
                @endisset
            </div>
        </div>
    </div>
@endsection