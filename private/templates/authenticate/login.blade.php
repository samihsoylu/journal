@extends('base')

@section('pageTitle', 'Login')

@section('content')
        <div class="valign-wrapper" style="width:100%;height:100%;position: absolute;">
            <div class="fullwidth">
                <div class="container">
                    <div class="row">
                        <div class="col s12 offset-m2 offset-l3">
                            <div class="alert-login-page">
@include('components/alerts')
                            </div>
                            <h3 class="center hidden show-on-tiny">Journal</h3>
                            <div class="card card-login">
                                <div class="card-login-splash hide-on-tiny">
                                    <div class="wrapper">
                                        <h1 class="site-title">Journal</h1>
                                    </div>
                                    <img src="{{ $assets_url }}/images/landscape.jpg" alt="landscape" />
                                </div>
                                <div class="card-content">
                                    <span class="card-title">Log In</span>
                                    <form action="{{ $login_post_url }}" method="post">
                                        <div class="input-field ">
                                            <input id="username" type="text" class="validate" name="username" required />
                                            <label for="username">Username</label>
                                        </div>
                                        <div class="input-field">
                                            <input id="password" type="password" class="validate" name="password" required />
                                            <label for="password">Password</label>
                                        </div>
                                        <div>
                                            <input class="btn btn-primary fullwidth" type="submit" value="Sign in" />
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <footer class="center-align">
                        <p>&copy; 2020 - {{ date('Y') }} <a href="https://github.com/samihsoylu/journal/" target="_blank">SS</a></p>
                    </footer>
                </div>
            </div>
        </div>
@endsection