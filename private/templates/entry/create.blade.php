@extends('base')

@section('pageTitle', 'Create entry')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l10 offset-m1 offset-l1">
                <form method="post" id="entry_form" action="{{ $create_entry_post_url }}">
                    <div class="row">
                        <div class="col s9 input-field">
                            <select id="template_id_d" name="template_id">
                                @foreach ($templates as $template)
                                    <option value="{{ $template->getId() }}">{{ $template->getTitle() }}</option>
                                @endforeach
                            </select>
                            <label for="template_id">Load a template</label>
                        </div>
                        <div class="col s3">
                            <button id="load-template" class="btn btn-primary mt-30" type="button"><i class="material-icons">update</i> Load</button>
                        </div>
                    </div>
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            @foreach ($categories as $category)
                            <option value="{{ $category->getId() }}">{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id">Category</label>
                    </div>
                    <div class="input-field">
                        <input id="entry_title" name="entry_title" type="text" class="validate" @isset($post['entry_title']) value="{{ $post['entry_title'] }}" @endisset  required />
                        <label for="entry_title">Entry title</label>
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

    <script src="{{ $assets_url }}/js/entry.js"></script>
    @include('components/tinymce')
@endsection