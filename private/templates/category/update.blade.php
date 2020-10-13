@extends('base')

@section('pageTitle', 'Update a category')

@section('content')
    @include('components/header')
    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12 m10 l7 offset-m1 offset-l2">
                @isset($category)
                <form method="post" action="{{ $category_url }}/{{ $category->getId() }}/update/action">
                    <div class="input-field">
                        <input id="category_title" name="category_name" type="text" class="validate" value="{{ $category->getName() }}" required />
                        <label for="category_title">Category title</label>
                    </div>
                    <div class="input-field">
                        <input id="category_description" name="category_description" type="text" class="validate" value="{{ $category->getDescription() }}" required />
                        <label for="category_description">Category description</label>
                    </div>
                    <div>
                        <button class="btn btn-default" type="button" onclick="@if(isset($success)) window.location.href='{{ $categories_url }}' @else window.history.go(-1); return false; @endif"><i class="material-icons">keyboard_arrow_left</i> Go back</button>
                        <button class="btn btn-danger" type="button" onclick="if (!confirm('DANGER: This action will delete the {{ $category->getName() }} category and all existing entries inside of it!')) { return false } else { window.location.href='{{ $category_url }}/{{ $category->getId() }}/delete'; }"><i class="material-icons">delete_forever</i> Delete</button>
                        <button class="btn btn-primary" type="submit"><i class="material-icons">save</i> Save</button>
                    </div>
                </form>
                @endisset
            </div>
        </div>
    </div>
@endsection