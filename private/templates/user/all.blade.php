@extends('base')

@section('pageTitle', 'Users')

@section('content')
    @include('components/header')

    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                <a href="{{ $create_user_url }}" class="btn btn-primary fullwidth hide-on-med-and-up"><i class="material-icons">add_box</i> Create</a>
                <a href="{{ $create_user_url }}" class="btn btn-primary right hide-on-small-only"><i class="material-icons">add_box</i> Create</a>
            </div>
        </div>
        <div class="row row-align">
            <div class="col s12">
                @isset($users)
                    @foreach ($users as $user)
                        <a href="{{ $user_url }}/{{ $user->getId() }}">
                            <div class="card">
                                <div class="card-content">
                                    <span class="card-title">{{ $user->getUsername() }}</span>
                                    <p>Created: {{ $user->getCreatedTimestampFormatted() }} <span title="Privilege" class="individual-entry-category">{{ $user->getPrivilegeLevelAsString() }}</span></p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endisset
                @empty($users)
                    <p>No users found.</p>
                @endempty
            </div>
        </div>
    </div>
@endsection