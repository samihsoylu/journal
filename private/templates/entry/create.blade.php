@extends('base')

@section('pageTitle', 'Create an entry')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <form method="post" action="{{ $create_entry_url }}">
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            @foreach ($categories as $category)
                            <option value="{{ $category->getId() }}">{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id_d">Category</label>
                    </div>
                    <div class="input-field">
                        <input id="entry_title" name="entry_title" type="text" class="validate" required />
                        <label for="entry_title">Entry title</label>
                    </div>
                    <div class="input-field">
                        <textarea id="entry_content" name="entry_content" class="materialize-textarea validate" required></textarea>
                        <label for="entry_content">Entry content</label>
                    </div>
                    <div>
                        <button class="btn btn-primary fullwidth" type="submit"><i class="material-icons">save</i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection