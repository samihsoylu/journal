@extends('base')

@section('pageTitle', 'Templates')

@section('content')
    @include('components/header')
    <div class="container">
        @include('components/create-buttons')
        <div class="row row-align">
            <div class="col s12">
            @foreach ($templates as $template)
                <a href="{{ $template_url }}/{{ $template->getId() }}/update">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">{{ $template->getName() }}</span>
                            <p>{{ $template->getContent() }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
            </div>
        </div>
    </div>
@endsection