@extends('base')

@section('pageTitle', 'Create a user')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <form method="post" action="{{ $create_user_post_url }}">
                    <div class="input-field">
                        <input id="email" type="email" name="email" value="{{ @$_POST['email'] }}" placeholder="example@domain.com" required />
                        <label for="email">Email address</label>
                    </div>
                    <div class="input-field">
                        <input id="username" type="text" name="username" value="{{ @$_POST['username'] }}" placeholder="example" required />
                        <label for="username">Username</label>
                    </div>
                    <div class="input-field">
                        <input id="password" type="password" name="password" placeholder="•••••••••" required />
                        <label for="password">Password</label>
                    </div>
                    <div class="input-field">
                        <select id="privilegeLevel" name="privilegeLevel">
                            @foreach($allowedPrivilegeLevels as $privilegeLevelId => $privilegeLevelName)
                                <option value="{{ $privilegeLevelId }}" @if($privilegeLevelId === (int)@$_POST['privilegeLevel']) selected @endif @if($session->getPrivilegeLevel() >= $privilegeLevelId) disabled @endif>{{ $privilegeLevelName }}</option>
                            @endforeach
                        </select>
                        <label for="privilegeLevel" class="dropdown-label">Privilege level</label>
                    </div>
                    <p>
                        <label>
                            <input name="forcePasswordChange" class="filled-in" type="checkbox" @if(isset($_POST['forcePasswordChange'])) checked="checked" @endif />
                            <span>Force password change on next login</span>
                        </label>
                    </p>

                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />

                    <!-- javascript in the back button ensures that the filters are not lost when you go back to the users page -->
                    <button class="btn btn-default" type="button" onclick="@if(isset($success)) window.location.href='{{ $users_url }}' @else window.history.go(-1); return false; @endif"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                    <button class="btn btn-primary" type="submit"><i class="material-icons">save</i> Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection