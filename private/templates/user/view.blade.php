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
                    <form method="post" action="#!">
                        <div class="input-field">
                            <input id="username" type="text" value="{{ $user->getUsername() }}" disabled />
                            <label for="username">Username</label>
                        </div>
                        <div class="input-field">
                            <input id="email" type="email" value="{{ $user->getEmailAddress() }}" disabled />
                            <label for="email">Email address</label>
                        </div>
                        <div class="input-field">
                            <input id="created" type="text" value="{{ $user->getCreatedTimestampFormatted() }}" disabled />
                            <label for="created">Created date</label>
                        </div>
                        <div class="input-field">
                            <select id="privilege" name="privilege" @if($isReadOnly) disabled @endif>
                                @foreach($user::ALLOWED_PRIVILEGE_LEVELS as $privilegeLevelId => $privilegeLevelName)
                                    <option value="{{ $privilegeLevelId }}" @if($privilegeLevelId === $user->getPrivilegeLevel()) selected @endif>{{ $privilegeLevelName }}</option>
                                @endforeach
                            </select>
                            <label for="privilege" class="dropdown-label">Privilege level</label>
                        </div>

                        <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />

                        <p class="small-text">Last updated: {{ $user->getLastUpdatedTimestampFormatted() }}</p>

                        <p class="small-text title"><b>Usage Statistics</b></p>
                        <p class="small-text context">Entries: {{ $totalEntries }} <br />Categories: {{ $totalCategories }}</p>

                        <!-- javascript in the back button ensures that the filters are not lost when you go back to the entries page -->
                        <button class="btn btn-default" type="button" onclick="@if(isset($success)) window.location.href='{{ $users_url }}' @else window.history.go(-1); return false; @endif"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                        <button class="btn btn-primary" type="button" onclick="window.location.href='{{ $user_url }}/{{ $user->getId() }}/update';" @if($isReadOnly) disabled @endif><i class="material-icons">save</i> Save</button>
                    </form>
                @endisset
            </div>
        </div>
    </div>
@endsection