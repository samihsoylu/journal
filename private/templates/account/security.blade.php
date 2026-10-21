@extends('base')
@section('pageTitle', 'Two-factor authentication')
@section('content')
@include('components/header')
<div class="container">
    <div class="row"><div class="col s12 m8 offset-m2">
        @include('components/alerts')
        <div class="card"><div class="card-content">
            <span class="card-title">Two-factor authentication</span>
            <p>Add a code from an authenticator app when you log in. Your password still unlocks your journal.</p>
            <p id="two-factor-status">Status: {{ $enabled ? 'enabled' : 'disabled' }}</p>
            @if($enabled)
                <p>Keep your recovery codes somewhere safe. Each works once. New recovery codes replace all previous ones.</p>
                <form method="post" action="{{ $security_regenerate_url }}">
                    <div class="input-field"><input id="security-password" name="password" type="password" autocomplete="current-password" required><label for="security-password">Current password</label></div>
                    <div class="input-field"><input id="security-code" name="code" type="text" autocomplete="one-time-code" maxlength="128" required><label for="security-code">Fresh authenticator or recovery code</label></div>
                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}">
                    <button class="btn" type="submit">New recovery codes</button>
                    <button class="btn btn-default mt-10" type="submit" formaction="{{ $security_disable_url }}">Disable two-factor login</button>
                </form>
            @elseif($setup !== null)
                <p>Add this setup key to your authenticator app. Choose time based codes with 6 digits and a 30 second period.</p>
                <p><code id="authenticator-secret" style="overflow-wrap:anywhere">{{ $setup['secret'] }}</code></p>
                <p><a href="{{ $setup['uri'] }}">Open in an authenticator app</a></p>
                <form method="post" action="{{ $security_confirm_url }}">
                    <div class="input-field"><input id="setup-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required><label for="setup-code">Code from your app</label></div>
                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}">
                    <button class="btn" type="submit">Enable and show recovery codes</button>
                </form>
                <form class="mt-10" method="post" action="{{ $security_cancel_url }}"><input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}"><button class="btn btn-default" type="submit">Cancel setup</button></form>
            @else
                <p>Before starting, make sure you can store the recovery codes. Without your app or a recovery code, you cannot log in.</p>
                <form method="post" action="{{ $security_begin_url }}">
                    <div class="input-field"><input id="setup-password" name="password" type="password" autocomplete="current-password" required><label for="setup-password">Current password</label></div>
                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}">
                    <button class="btn" type="submit">Set up authenticator</button>
                </form>
            @endif
        </div><div class="card-action"><a href="{{ $account_url }}">Back to account</a></div></div>
    </div></div>
</div>
@endsection
