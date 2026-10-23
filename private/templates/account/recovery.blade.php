@extends('base')
@section('pageTitle', 'Save your recovery codes')
@section('content')
@include('components/header')
<div class="container"><div class="row"><div class="col s12 m8 offset-m2">
    <div class="card"><div class="card-content">
        <span class="card-title">Save your recovery codes</span>
        <p>Two-factor login is enabled. These codes are shown only now. Keep them somewhere safe, separate from your password. Each code works once if you lose access to your app.</p>
        <ul id="recovery-codes" style="overflow-wrap:anywhere">
            @foreach($recoveryCodes as $code)
                <li><code>{{ $code }}</code></li>
            @endforeach
        </ul>
        <p>If you lose these codes, create new ones from your account while you can still log in.</p>
    </div><div class="card-action"><a href="{{ $security_url }}">I saved these codes</a></div></div>
</div></div></div>
@endsection
