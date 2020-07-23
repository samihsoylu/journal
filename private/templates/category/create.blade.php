@extends('base')

@section('pageTitle', 'Create an entry')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                <form method="post" action="{{ $create_category_post_url }}">
                    <div class="input-field">
                        <input id="category_title" name="category_name" type="text" class="validate" required />
                        <label for="category_title">Category title</label>
                    </div>
                    <div class="input-field">
                        <input id="category_description" name="category_description" type="text" class="validate" required />
                        <label for="category_description">Category description</label>
                    </div>
                    <div>
                        <button class="btn btn-primary fullwidth" type="submit"><i class="material-icons">save</i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection