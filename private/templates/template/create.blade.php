@extends('base')

@section('pageTitle', 'Create template')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l10 offset-m1 offset-l1">
                <form method="post" id="entry_form" action="{{ $create_template_post_url }}">
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            @foreach ($categories as $category)
                            <option value="{{ $category->getId() }}">{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id">Default entry category</label>
                    </div>
                    <div class="input-field">
                        <input id="template_title" name="template_title" type="text" class="validate" @isset($post['template_title']) value="{{ $post['template_title'] }}" @endisset  required />
                        <label for="template_title">Template title</label>
                    </div>
                    <div class="input-field">
                        <textarea id="entry_content" name="entry_content">@isset($post['entry_content']) {{ $post['entry_content'] }} @endisset</textarea>
                    </div>
                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
                    <div>
                        <button class="btn btn-primary" type="submit"><i class="material-icons">save</i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('jquery-scripts')
    @include('components/confirm-reload')
    @include('components/tinymce')
@endsection