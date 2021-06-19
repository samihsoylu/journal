@extends('base')

@section('pageTitle', 'Categories')

@section('content')
    @include('components/header')
    <div class="container">
        @include('components/create-buttons')
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