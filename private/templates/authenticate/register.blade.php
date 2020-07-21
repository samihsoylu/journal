@extends('base')

@section('pageTitle', 'Login')

@section('content')
    <div class="col-12">
        <div class="auth-form mt-6">
            <h1>Register</h1>
            <div class="Box box-shadow mt-3">
                <div class="Box-row">
                    @include('components/alerts')
                    <form action="{{ $register_post_url }}" method="post">
                        <label for="email">Email</label>
                        <input class="form-control input-block" type="text" id="email" name="email" />

                        <label for="username">Username</label>
                        <input class="form-control input-block" type="text" id="username" name="username" />

                        <label for="password">Password</label>
                        <input class="form-control input-block" type="password" id="password" name="password" />

                        <button class="btn btn-primary btn-block" type="submit">Register</button>
                    </form>
                </div>
            </div>
            <footer>&copy; 2020 <a href="https://github.com/samihsoylu/" target="_blank">SS</a></footer>
        </div>
    </div>
@endsection