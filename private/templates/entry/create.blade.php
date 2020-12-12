@extends('base')

@section('pageTitle', 'Create entry')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <form method="post" action="{{ $create_entry_post_url }}">
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
                        <textarea id="entry_content" name="entry_content" style="min-height:250px;" class="materialize-textarea validate" required>@isset($post['entry_content']) {{ $post['entry_content'] }} @endisset</textarea>
                        <label for="entry_content">Entry content</label>
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
@endsection