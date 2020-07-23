@extends('base')

@section('pageTitle', 'Update a category')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                @isset($category)
                <form method="post" action="{{ $update_category_url }}/{{ $category->getId() }}/post">
                    <div class="input-field">
                        <input id="category_title" name="category_name" type="text" class="validate" value="{{ $category->getName() }}" required />
                        <label for="category_title">Category title</label>
                    </div>
                    <div class="input-field">
                        <input id="category_description" name="category_description" type="text" class="validate" value="{{ $category->getDescription() }}" required />
                        <label for="category_description">Category description</label>
                    </div>
                    <div>
                        <button class="btn btn-primary fullwidth" type="submit"><i class="material-icons">save</i> Save</button>
                    </div>
                </form>
                @endisset
            </div>
        </div>
    </div>
@endsection