@extends('base')

@section('pageTitle', 'Dashboard')

@section('content')
            @include('components/header')
            <div class="container large">
                <div class="section">
                    <div class="row row-align">
                        <div class="col s12 m6 l3">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/journal-handwriting-woman.jpg">
                                    <span class="card-title">Create an entry</span>
                                    <a href="{{ $create_entry_url }}" class="btn-floating halfway-fab waves-effect waves-light green"><i class="material-icons">add</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>Organise your thoughts, feelings and opinions.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l3">
                            <div class="card">
                                <div class="card-image">
                                    <img src="{{ $assets_url }}/images/chapter-of-a-book.jpg">
                                    <span class="card-title">Create a category</span>
                                    <a href="{{ $create_category_url }}" class="btn-floating halfway-fab waves-effect waves-light brown"><i class="material-icons">playlist_add</i></a>
                                </div>
                                <div class="dashboard card-content">
                                    <p>Break up your entries into smaller pieces. Like chapters of a book.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col s12 m6 l3">
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

                        @if ($session->userHasAdminPrivileges())
                            <div class="col s12 m6 l3">
                                <div class="card">
                                    <div class="card-image">
                                        <img src="{{ $assets_url }}/images/friends.jpg">
                                        <span class="card-title">Manage users</span>
                                        <a href="{{ $users_url }}" class="btn-floating halfway-fab waves-effect waves-light blue-grey"><i class="material-icons">people</i></a>
                                    </div>
                                    <div class="dashboard card-content">
                                        <p>Invite friends or family to join in on this journey of keeping an organised mind.</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col s12 m6 l3">
                                <div class="card">
                                    <div class="card-image">
                                        <img src="{{ $assets_url }}/images/setting.jpg">
                                        <span class="card-title">Account settings</span>
                                        <a href="{{ $account_url }}" class="btn-floating halfway-fab waves-effect waves-light purple"><i class="material-icons">person</i></a>
                                    </div>
                                    <div class="dashboard card-content">
                                        <p>Explore account specific settings</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="row row-align">
                        <div class="col s12">
                            @foreach ($entries->getEntries() as $entry)
                                <a href="{{ $entry_url }}/{{ $entry->getId() }}">
                                    <div class="card">
                                        <div class="card-content">
                                            <span class="card-title">{{ $entry->getTitle() }}</span>
                                            <p> {{ $entry->getCreatedTimestampFormatted() }} <span title="Category" class="card-text-right">{{ $entry->getReferencedCategory()->getName() }}</span></p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div><!-- /.section -->
            </div>
@endsection