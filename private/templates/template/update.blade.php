@extends('base')

@section('pageTitle', 'Update template')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <form method="post" action="{{ $template_url }}/{{ $template->getId() }}/update/action">
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            @foreach ($categories as $category)
                                <option value="{{ $category->getId() }}" @if(isset($post['category_id']) && (int)$post['category_id'] === $category->getId()) selected @elseif($template->getCategoryId() === $category->getId()) selected @endif>{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id">Category</label>
                    </div>
                    <div class="input-field">
                        <input id="template_title" name="template_title" type="text" class="validate" @if(isset($post['template_title'])) value="{{ $post['template_title'] }}" @else value="{{ $template->getTitle() }}" @endif  required />
                        <label for="template_title">Template title</label>
                    </div>
                    <div class="input-field">
                        <textarea id="template_content" name="template_content" style="min-height:250px;" class="materialize-textarea validate" required>@if(isset($post['template_content'])){{ $post['template_content'] }}@else{{ $template->getContent() }}@endif</textarea>
                        <label for="template_content">Template content</label>
                    </div>
                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
                    <div>
                        <button class="btn btn-default" type="button" onclick="window.history.go(-1);"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                        <button class="btn btn-danger" id="delete" type="button" onclick="deleteButton('DANGER: This action will delete this template', '{{ $template_url }}/{{ $template->getId() }}/delete/{{ $session->getAntiCSRFToken() }}')"><i class="material-icons">delete_forever</i> Delete</button>
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
    @include('components/delete-button-warning')
@endsection