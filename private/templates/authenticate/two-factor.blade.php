@extends('base')
@section('pageTitle', 'Verify your login')
@section('content')
<div class="container">
    <div class="row"><div class="col s12 m8 offset-m2 l6 offset-l3">
        <div class="card"><div class="card-content">
            <span class="card-title">Verify your login</span>
            @include('components/alerts')
            <p>Enter a fresh code from your authenticator app, or one unused recovery code.</p>
            <form method="post" action="{{ $two_factor_url }}">
                <div class="input-field">
                    <input id="code" name="code" type="text" autocomplete="one-time-code" maxlength="128" required autofocus>
                    <label for="code">Authenticator or recovery code</label>
                </div>
                <input type="hidden" name="form_key" value="{{ $token }}">
                <button class="btn" type="submit">Verify</button>
            </form>
            <form class="mt-10" method="post" action="{{ $two_factor_cancel_url }}">
                <input type="hidden" name="form_key" value="{{ $token }}">
                <button class="btn btn-default" type="submit">Cancel login</button>
            </form>
        </div></div>
    </div></div>
</div>
@endsection
