@extends('base')

@section('pageTitle', 'Entries')

@section('content')
    @include('components/header')

    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                <a href="{{ $create_entry_url }}" class="btn btn-primary fullwidth hide-on-med-and-up"><i class="material-icons">add_box</i> Create</a>
                <a href="{{ $create_entry_url }}" class="btn btn-primary right hide-on-small-only"><i class="material-icons">add_box</i> Create</a>
            </div>
        </div>
        <div class="row row-align">

            @isset($entries)
            <!-- Mobile Filter Menu -->
            <div class="col s12 hide-on-med-and-up">
                <ul id="filters-slide-out" class="sidenav filters">
                    <li><a class="side-menu-title">Filters</a></li>
                    <form id="filters" method="GET">

                        <!-- Mobile Filter: Search bar -->
                        <li class="input-field ">
                            <input id="search_m" name="search_by_title" type="text" />
                            <label for="search_m">Filter by title (optional)</label>
                        </li>

                        <!-- Mobile Filter: Dropdown -->
                        <li class="input-field">
                            <select id="entries_limit_m" name="entries_limit">
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="150">150</option>
                                <option value="200">200</option>
                                <option value="250">250</option>
                            </select>
                            <label for="entries_limit_m" class="dropdown-label">Show entries (required)</label>
                        </li>

                        <!-- Mobile Filter: Dropdown -->
                        <li class="input-field">
                            <select id="category_id_m" name="category_id">
                                <option value="" selected>-</option>
                                <option value="1">Personal</option>
                                <option value="2">Diet</option>
                                <option value="3">Dreams</option>
                                <option value="4">Work</option>
                            </select>
                            <label for="category_id_m" class="dropdown-label">Filter by category (optional)</label>
                        </li>

                        <!-- Mobile Filter: Datepicker -->
                        <li class="input-field">
                            <input id="date_from_m" type="text" name="date_from" class="datepicker">
                            <label for="date_from_m">Date from (optional)</label>
                        </li>

                        <!-- Mobile Filter: Datepicker -->
                        <li class="input-field">
                            <input id="date_to_m" type="text" name="date_to" class="datepicker">
                            <label for="date_to_m">Date to (optional)</label>
                        </li>

                        <!-- Mobile Filter: Button -->
                        <li>
                            <input class="btn btn-primary fullwidth" type="submit" value="Filter" />
                        </li>
                    </form>
                </ul>
                <!-- filter menu button with corner position on mobile -->
                <a href="#" data-target="filters-slide-out" class="sidenav-trigger valign-wrapper btn-floating btn-large waves-effect waves-light green corner-position"><i class="material-icons">filter_list</i></a>
            </div>

            <!-- Desktop filters -->
            <div class="col hide-on-small-only m4 l3">
                <form id="filters" method="GET">
                    <div class="input-field ">
                        <input id="search_d" name="search_by_title" type="text" />
                        <label for="search_d">Filter by title (optional)</label>
                    </div>
                    <div class="input-field">
                        <select id="entries_limit_d" name="entries_limit">
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="150">150</option>
                            <option value="200">200</option>
                            <option value="250">250</option>
                        </select>
                        <label for="entries_limit_d">Show entries (required)</label>
                    </div>
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            <option value="" selected>-</option>
                            <option value="1">Personal</option>
                            <option value="2">Diet</option>
                            <option value="3">Dreams</option>
                            <option value="4">Work</option>
                        </select>
                        <label for="category_id_d">Filter by category (optional)</label>
                    </div>
                    <div class="input-field">
                        <input id="date_from_d" type="text" name="date_from" class="datepicker">
                        <label for="date_from_d">Date from (optional)</label>
                    </div>
                    <div class="input-field">
                        <input id="date_to_d" type="text" name="date_to" class="datepicker">
                        <label for="date_to_d">Date to (optional)</label>
                    </div>
                    <div>
                        <input class="btn btn-primary fullwidth" type="submit" value="Filter" />
                    </div>
                </form>
            </div>
            @endisset

            <div class="col s12 m8 l9">
                @isset($entries)
                    @foreach ($entries as $entry)
                        <a href="{{ $entry_url }}/{{ $entry->getId() }}">
                            <div class="card">
                                <div class="card-content">
                                    <span class="card-title">{{ $entry->getTitle() }}</span>
                                    <p>{{ $entry->getCreatedTimestamp() }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endisset
                @empty($entries)
                        <p>No entries found, you still need to create your first entry.</p>
                @endempty

                @isset($pages)
                    <div class="row">
                        <div class="col s12">
                            <ul class="pagination center">
                                @foreach ($pages as $page)
                                    <li class="waves-effect"><a href="#!">{{ $page->getNumber() }}</a></li>
                                @endforeach
                                <li class="disabled"><a href="#!"><i class="material-icons">chevron_left</i></a></li>
                                <li class="active"><a href="#!">1</a></li>
                                <li class="waves-effect"><a href="#!">2</a></li>
                                <li class="waves-effect"><a href="#!">3</a></li>
                                <li class="waves-effect"><a href="#!"><i class="material-icons">chevron_right</i></a></li>
                            </ul>
                        </div>
                    </div>
                @endisset

            </div>
        </div>
    </div>
@endsection