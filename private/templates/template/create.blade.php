@extends('base')

@section('pageTitle', 'Create template')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <form method="post" action="{{ $create_template_post_url }}">
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            @foreach ($categories as $category)
                            <option value="{{ $category->getId() }}">{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id">Category</label>
                    </div>
                    <div class="input-field">
                        <input id="template_title" name="template_title" type="text" class="validate" @isset($post['template_title']) value="{{ $post['template_title'] }}" @endisset  required />
                        <label for="template_title">Template title</label>
                    </div>
                    <div class="input-field">
                        <textarea id="template_content" name="template_content" style="min-height:250px;" class="materialize-textarea validate" required>@isset($post['template_content']) {{ $post['template_content'] }} @endisset</textarea>
                        <label for="template_content">Template content</label>
                    </div>
                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
                    <div>
                        <button class="btn btn-primary" type="submit"><i class="material-icons">save</i> Save</button>
                        @include('components/markdown-modal')
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('jquery-scripts')
    @include('components/confirm-reload')
@endsection