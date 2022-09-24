@extends('base')

@section('pageTitle', 'Update entry')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l10 offset-m1 offset-l1">
                @isset($entry)
                <form method="post" id="entry_form" action="{{ $entry_url }}/{{ $entry->getId() }}/update/action">
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            @foreach ($categories as $category)
                            <option value="{{ $category->getId() }}" @if(isset($post['category_id']) && (int)$post['category_id'] === $category->getId()) selected @elseif($entry->getCategoryId() === $category->getId()) selected @endif>{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id">Category</label>
                    </div>
                    <div class="input-field">
                        <input id="entry_title" name="entry_title" type="text" class="validate" @if(isset($post['entry_title'])) value="{{ $post['entry_title'] }}" @else value="{{ $entry->getTitle() }}" @endif  required />
                        <label for="entry_title">Entry title</label>
                    </div>
                    <div class="input-field">
                        <textarea id="entry_content" name="entry_content">@if(isset($post['entry_content'])){{ $post['entry_content'] }}@else{{ $entry->getContent() }}@endif</textarea>
                    </div>
                    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
                    <div>
                        <button class="btn btn-default" type="button" onclick="window.history.go(-1);"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                        <button class="btn btn-danger" id="delete" type="button" onclick="deleteButton('DANGER: This action will delete this entry', '{{ $entry_url }}/{{ $entry->getId() }}/delete/{{ $session->getAntiCSRFToken() }}')"><i class="material-icons">delete_forever</i> Delete</button>
                        <button class="btn btn-primary" type="submit"><i class="material-icons">save</i> Save</button>
                    </div>
                </form>
                @endisset
            </div>
        </div>
    </div>
@endsection

@section('jquery-scripts')
    @include('components/confirm-reload')
    @include('components/delete-button-warning')
    @include('components/tinymce')
@endsection