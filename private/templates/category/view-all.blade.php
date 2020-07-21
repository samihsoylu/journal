@extends('base')

@section('pageTitle', 'Categories')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                <button class="btn btn-primary fullwidth hide-on-med-and-up" onclick="window.location.href='{{ $create_category_url }}'"><i class="material-icons">playlist_add</i> Create</button>
                <button class="btn btn-primary right hide-on-small-only" onclick="window.location.href='{{ $create_category_url }}'"><i class="material-icons">playlist_add</i> Create</button>
            </div>
        </div>
        <div class="row row-align">
            <div class="col s12">
            @foreach ($categories as $category)
                <a href="{{ $update_category_clean_url }}/{{ $category->getId() }}">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">{{ $category->getCategoryName() }}</span>
                            <p>{{ $category->getCategoryDescription() }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
            </div>
        </div>
    </div>
@endsection