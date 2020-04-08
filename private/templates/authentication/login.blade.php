@extends('base')

@section('pageTitle', 'Login')

@section('content')
    <div class="col-12">
        <div class="auth-form mt-6">
            <h1>Sign in to Journal</h1>
            <div class="Box box-shadow mt-3">
                <div class="Box-row">
                    <form action="{{ $post_url }}" method="post">
                        <label for="username">Username</label>
                        <input class="form-control input-block" type="text" id="username" />

                        <label for="password">Password</label>
                        <input class="form-control input-block" type="password" id="password" />

                        <button class="btn btn-primary btn-block" type="submit">Sign in</button>
                    </form>
                </div>
            </div>
            <footer>&copy; 2020 <a href="https://github.com/samihsoylu/" target="_blank">SS</a></footer>
        </div>
    </div>
@endsection