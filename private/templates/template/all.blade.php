@extends('base')

@section('pageTitle', 'Templates')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                <button class="btn btn-primary fullwidth hide-on-med-and-up mt-10" onclick="window.location.href='{{ $create_template_url }}'"><i class="material-icons">playlist_add</i> Create Template</button>
                <button class="btn btn-primary right hide-on-small-only mr-10" onclick="window.location.href='{{ $create_template_url }}'"><i class="material-icons">playlist_add</i> Create Template</button>
            </div>
        </div>
        <div class="row row-align">
            <div class="col s12">
            @foreach ($templates as $template)
                <a href="{{ $template_url }}/{{ $template->getId() }}/update">
                    <div class="card">
                        <div class="card-content">
                            <span class="card-title">{{ $template->getTitle() }}</span>
                            <p class="light-grey-text italic">{{ $template->getReferencedCategory()->getName() }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
            </div>
        </div>
    </div>
@endsection