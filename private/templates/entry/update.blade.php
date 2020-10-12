@extends('base')

@section('pageTitle', 'Update entry')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <form method="post" action="{{ $entry_url }}/{{ $entry->getId() }}/update/action">
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            @foreach ($categories as $category)
                            <option value="{{ $category->getId() }}" @if(isset($post['category_id']) && (int)$post['category_id'] === $category->getId()) selected @elseif($entry->getReferencedCategory()->getId() === $category->getId()) selected @endif>{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id">Category</label>
                    </div>
                    <div class="input-field">
                        <input id="entry_title" name="entry_title" type="text" class="validate" @if(isset($post['entry_title'])) value="{{ $post['entry_title'] }}" @else value="{{ $entry->getTitle() }}" @endif  required />
                        <label for="entry_title">Entry title</label>
                    </div>
                    <div class="input-field">
                        <textarea id="entry_content" name="entry_content" style="min-height:100px;" class="materialize-textarea validate" required>@if(isset($post['entry_content'])) {{ $post['entry_content'] }} @else {{ $entry->getContent() }} @endif</textarea>
                        <label for="entry_content">Entry content</label>
                    </div>
                    <div>
                        <button class="btn btn-primary" type="submit"><i class="material-icons">save</i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection