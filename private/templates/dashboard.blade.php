@extends('base')

@section('pageTitle', 'Dashboard')

@section('content')
            @include('components/header')
            <div class="container">
                <div class="section">
                    <div class="row row-align">
                        <div class="col s12 m6 l4">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/journal-handwriting-woman.jpg">
                                    <span class="card-title">Create an entry</span>
                                    <a class="btn-floating halfway-fab waves-effect waves-light green"><i class="material-icons">add</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>Organise your thoughts, feelings and opinions.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l4">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/chapter-of-a-book.jpg">
                                    <span class="card-title">Create a category</span>
                                    <a class="btn-floating halfway-fab waves-effect waves-light brown"><i class="material-icons">playlist_add</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>Break up your entries into smaller pieces. Like chapters of a book.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l4">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/book-case.jpg">
                                    <span class="card-title">View all entries</span>
                                    <a href="{{ $entries_url }}" class="btn-floating halfway-fab waves-effect waves-light blue"><i class="material-icons">open_in_browser</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>See an overview of your already written notes.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l4">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/friends.jpg">
                                    <span class="card-title">Manage users</span>
                                    <a class="btn-floating halfway-fab waves-effect waves-light blue-grey"><i class="material-icons">people</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>Invite friends or family to join in on this journey of keeping an organised mind.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l4">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/setting.jpg">
                                    <span class="card-title">Account settings</span>
                                    <a class="btn-floating halfway-fab waves-effect waves-light purple"><i class="material-icons">person</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>Explore account specific settings</p>
                                </div>
                            </div>
                        </div>


                        <div class="col s12 m6 l4">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/exit.jpg">
                                    <span class="card-title">Logout</span>
                                    <a href="{{ $logout_url }}" class="btn-floating halfway-fab waves-effect waves-light red"><i class="material-icons">exit_to_app</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>Are you finished? Safely logout here.</p>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.section -->
            </div>
@endsection