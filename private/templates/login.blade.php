@extends('base')

@section('pageTitle', 'Login')

@section('content')
    <div class="col-12">
        <div class="auth-form mt-6">
            <h1>Notes</h1>
            <div class="Box box-shadow mt-3">
                <div class="Box-row">
                    <form action="{{ $post_url }}">
                        <label for="password">Password</label>
                        <input class="form-control input-block" type="password" id="password" />

                        <button class="btn btn-primary btn-block mt-3" type="submit">Sign in</button>
                    </form>
                </div>
            </div>
            <footer>&copy; 2020 <a href="https://github.com/samihsoylu/" target="_blank">SS</a></footer>
        </div>
    </div>
@endsection