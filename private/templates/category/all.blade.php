@extends('base')

@section('pageTitle', 'Categories')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                <button class="btn btn-primary fullwidth hide-on-med-and-up" onclick="window.location.href='{{ $create_entry_url }}'"><i class="material-icons">add_box</i> Create Entry</button>
                <button class="btn btn-primary right hide-on-small-only" onclick="window.location.href='{{ $create_entry_url }}'"><i class="material-icons">add_box</i> Create Entry</button>
                <button class="btn btn-primary fullwidth hide-on-med-and-up mt-10" onclick="window.location.href='{{ $create_category_url }}'"><i class="material-icons">playlist_add</i> Create Category</button>
                <button class="btn btn-primary right hide-on-small-only mr-10" onclick="window.location.href='{{ $create_category_url }}'"><i class="material-icons">playlist_add</i> Create Category</button>
            </div>
        </div>
        <div class="row row-align">
            <div class="col s12">
            @foreach ($categories as $category)
                <a href="{{ $category_url }}/{{ $category->getId() }}/update">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">{{ $category->getName() }}</span>
                            <p>{{ $category->getDescription() }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
            </div>
        </div>
    </div>
@endsection