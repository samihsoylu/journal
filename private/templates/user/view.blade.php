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
                    <form method="post" action="{{ $user_url }}/{{ $user->getId() }}/update">
                        <div class="input-field">
                            <input id="username" type="text" value="{{ $user->getUsername() }}" disabled />
                            <label for="username">Username</label>
                        </div>
                        <div class="input-field">
                            <input id="email" type="email" value="{{ $user->getEmailAddress() }}" disabled />
                            <label for="email">Email address</label>
                        </div>
                        <div class="input-field">
                            <input id="created" type="text" value="{{ $user->getCreatedTimestamp() }}" disabled />
                            <label for="created">Created date</label>
                        </div>
                        <div class="input-field">
                            <select id="privilege" name="privilege" @if($user->isReadOnly()) disabled @endif>
                                @foreach($user::ALLOWED_PRIVILEGE_LEVELS as $privilegeLevelId => $privilegeLevelName)
                                    <option value="{{ $privilegeLevelId }}" @if($privilegeLevelId === $user->getPrivilegeLevel()) selected @endif @if($session->getPrivilegeLevel() >= $privilegeLevelId) disabled @endif>{{ $privilegeLevelName }}</option>
                                @endforeach
                            </select>
                            <label for="privilege" class="dropdown-label">Privilege level</label>
                        </div>

                        <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />

                        <p class="small-text">Last updated: {{ $user->getLastUpdatedTimestamp() }}</p>

                        <p class="small-text title"><b>Usage Statistics</b></p>
                        <p class="small-text context">Entries: {{ $user->getTotalEntries() }} <br />Categories: {{ $user->getTotalCategories() }}</p>

                        <!-- javascript in the back button ensures that the filters are not lost when you go back to the users page -->
                        <button class="btn btn-default" type="button" onclick="@if(isset($success)) window.location.href='{{ $users_url }}' @else window.history.go(-1); return false; @endif"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                        <button class="btn btn-danger" id="delete" type="button" onclick="deleteButton('DANGER: This action will delete this user', '{{ $user_url }}/{{ $user->getId() }}/delete/{{ $session->getAntiCSRFToken() }}')" @if($user->isReadOnly()) disabled @endif><i class="material-icons">delete_forever</i> Delete</button>
                        <button class="btn btn-primary" type="submit" @if($user->isReadOnly()) disabled @endif><i class="material-icons">save</i> Save</button>
                    </form>
                @endisset
            </div>
        </div>
    </div>
@endsection

@section('jquery-scripts')
    @include('components/confirm-reload')
@endsection