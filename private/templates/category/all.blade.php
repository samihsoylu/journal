@extends('base')

@section('pageTitle', 'Categories')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                <button class="btn btn-primary fullwidth hide-on-med-and-up mt-10" onclick="window.location.href='{{ $create_category_url }}'"><i class="material-icons">playlist_add</i> Create Category</button>
                <button class="btn btn-primary right hide-on-small-only mr-10" onclick="window.location.href='{{ $create_category_url }}'"><i class="material-icons">playlist_add</i> Create Category</button>
            </div>
        </div>
        <div class="row row-align">
            <div class="col s12">
                <div id="sortable" class="ui-sortable">
                @foreach ($categories as $category)
                    <div id="{{ $category->getId() }}" class="card sortable-card">
                        <div class="card-icon ui-sortable-handle">
                            <i class="material-icons">reorder</i>
                        </div>
                        <a href="{{ $category_url }}/{{ $category->getId() }}/update">
                            <div class="card-content">
                                <span class="card-title">{{ $category->getName() }}</span>
                                <p>{{ $category->getDescription() }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('jquery-scripts')
    <script src="{{ $assets_url }}/js/category.js"></script>
@endsection