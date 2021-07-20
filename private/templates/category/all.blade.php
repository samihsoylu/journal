@extends('base')

@section('pageTitle', 'Categories')

@section('content')
    @include('components/header')
    <div class="container">
        @include('components/create-buttons')
        <div class="row row-align">
            <div class="col s12">
                <div id="sortable" class="ui-sortable">
                @foreach ($categories as $category)
                    {{-- Don't show <uncategorized> category in Categories page --}}
                    @if ($category->getName() == '<uncategorized>')
                        @continue
                    @endif
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