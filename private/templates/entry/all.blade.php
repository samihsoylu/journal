@extends('base')

@section('pageTitle', 'Entries')

@section('content')
    @include('components/header')

    <div class="container">
        <div class="row row-align button-row">
            <div class="col s12">
                <button class="btn btn-primary fullwidth hide-on-med-and-up" onclick="window.location.href='{{ $create_entry_url }}'"><i class="material-icons">add_box</i> Create Entry</button>
                <button class="btn btn-primary right hide-on-small-only" onclick="window.location.href='{{ $create_entry_url }}'"><i class="material-icons">add_box</i> Create Entry</button>
            </div>
        </div>
        <div class="row row-align">
            <!-- Mobile Filter Menu -->
            <div class="col s12 hide-on-med-and-up">
                <ul id="filters-slide-out" class="sidenav filters">
                    <li><a class="side-menu-title">Filters</a></li>
                    <form id="filters" method="GET">

                        <!-- Mobile Filter: Search bar -->
                        <li class="input-field ">
                            <input id="search_m" name="search_by_title" type="text" @isset($get['search_by_title']) value="{{ $get['search_by_title'] }}" @endisset />
                            <label for="search_m">Filter by title (optional)</label>
                        </li>

                        <!-- Mobile Filter: Dropdown -->
                        <li class="input-field">
                            <select id="page_size_m" name="page_size">
                                <option value="25" @if(@$get['page_size'] === '25') selected @endif>25</option>
                                <option value="50" @if(@$get['page_size'] === '50') selected @endif>50</option>
                                <option value="100" @if(@$get['page_size'] === '100') selected @endif>100</option>
                                <option value="150" @if(@$get['page_size'] === '150') selected @endif>150</option>
                                <option value="200" @if(@$get['page_size'] === '200') selected @endif>200</option>
                                <option value="250" @if(@$get['page_size'] === '250') selected @endif>250</option>
                            </select>
                            <label for="page_size_m" class="dropdown-label">Show entries (required)</label>
                        </li>

                        <!-- Mobile Filter: Dropdown -->
                        <li class="input-field">
                            <select id="category_id_m" name="category_id">
                                <option value="" selected>All</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->getId() }}" @if(@$get['category_id'] == $category->getId()) selected @endif>{{ $category->getName() }}</option>
                                @endforeach
                            </select>
                            <label for="category_id_m" class="dropdown-label">Filter by category (optional)</label>
                        </li>

                        <!-- Mobile Filter: Datepicker -->
                        <li class="input-field">
                            <input id="date_from_m" type="text" name="date_from" class="datepicker" @isset($get['date_from']) value="{{ $get['date_from'] }}" @endisset>
                            <label for="date_from_m">Date from (optional)</label>
                        </li>

                        <!-- Mobile Filter: Datepicker -->
                        <li class="input-field">
                            <input id="date_to_m" type="text" name="date_to" class="datepicker" @isset($get['date_to']) value="{{ $get['date_to'] }}" @endisset>
                            <label for="date_to_m">Date to (optional)</label>
                        </li>

                        <!-- Mobile Filter: Button -->
                        <li>
                            <input class="btn btn-primary fullwidth" type="submit" value="Filter" />
                            <button class="btn btn-default fullwidth" type="button" onclick="window.location='{{ $entries_url }}'">Reset page</button>
                        </li>
                    </form>
                </ul>
                <!-- filter menu button with corner position on mobile -->
                <a href="#" data-target="filters-slide-out" class="sidenav-trigger valign-wrapper btn-floating btn-large waves-effect waves-light green corner-position"><i class="material-icons">filter_list</i></a>
            </div>

            <!-- Desktop filters -->
            <div class="col hide-on-small-only m4 l3">
                <form id="desktopFilter" method="GET">
                    <div class="input-field ">
                        <input id="search_d" name="search_by_title" type="text"  @isset($get['search_by_title']) value="{{ $get['search_by_title'] }}" @endisset />
                        <label for="search_d">Filter by title (optional)</label>
                    </div>
                    <div class="input-field">
                        <select id="page_size_d" name="page_size">
                            <option value="25" @if(@$get['page_size'] === '25') selected @endif>25</option>
                            <option value="50" @if(@$get['page_size'] === '50') selected @endif>50</option>
                            <option value="100" @if(@$get['page_size'] === '100') selected @endif>100</option>
                            <option value="150" @if(@$get['page_size'] === '150') selected @endif>150</option>
                            <option value="200" @if(@$get['page_size'] === '200') selected @endif>200</option>
                            <option value="250" @if(@$get['page_size'] === '250') selected @endif>250</option>
                        </select>
                        <label for="page_size_d">Show entries (required)</label>
                    </div>
                    <div class="input-field">
                        <select id="category_id_d" name="category_id">
                            <option value="" selected>All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->getId() }}" @if(@$get['category_id'] == $category->getId()) selected @endif>{{ $category->getName() }}</option>
                            @endforeach
                        </select>
                        <label for="category_id_d">Filter by category (optional)</label>
                    </div>
                    <div class="input-field">
                        <input id="date_from_d" type="text" name="date_from" class="datepicker" @isset($get['date_from']) value="{{ $get['date_from'] }}" @endisset>
                        <label for="date_from_d">Date from (optional)</label>
                    </div>
                    <div class="input-field">
                        <input id="date_to_d" type="text" name="date_to" class="datepicker" @isset($get['date_to']) value="{{ $get['date_to'] }}" @endisset>
                        <label for="date_to_d">Date to (optional)</label>
                    </div>
                    <div>
                        <input class="btn btn-primary fullwidth" type="submit" value="Filter" />
                    </div>
                </form>
            </div>

            <div class="col s12 m8 l9">
                @isset($enabledWidgets['quickAddEntriesBoxEntriesOverview'])
                    @include('widgets/quick-add-entries-box')
                @endisset
                @isset($entries)
                    @foreach ($entries->getEntries() as $entry)
                        <a href="{{ $entry_url }}/{{ $entry->getId() }}">
                            <div class="card">
                                <div class="card-content">
                                    <span class="card-title">{{ $entry->getTitle() }}</span>
                                    <p>{{ $entry->getCreatedTimestampFormatted() }} <span title="Category" class="card-text-right light-grey-text italic">{{ $entry->getReferencedCategory()->getName() }}</span></p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endisset
                @empty($entries->getEntries())
                    <p>No entries found.</p>
                @endempty

                @php($leftIconUrl  = $entries->getPreviousPageUrl())
                @php($rightIconUrl = $entries->getNextPageUrl())

                @php($leftIconClass  = 'waves-effect')
                @php($rightIconClass = 'waves-effect')

                @if($entries->getCurrentPage() === 1)
                    @php($leftIconClass = 'disabled')
                    @php($leftIconUrl   = '#!')
                @endif
                @if($entries->getCurrentPage() === $entries->getTotalPages())
                    @php($rightIconClass = 'disabled')
                    @php($rightIconUrl   = '#!')
                @endif
                <div class="row">
                    <div class="col s12">
                        <ul class="pagination center">
                            <li class="{{ $leftIconClass }}"><a href="{{ $leftIconUrl }}"> <i class="material-icons">chevron_left</i></a></li>
                            @for ($i = 1; $i <= $entries->getTotalPages(); $i++)
                                <li class="@if($entries->getCurrentPage() === $i) active @else waves-effect @endif"><a href="{{ $entries->getPaginationFilterUri() }}page={{ $i }}">{{ $i }}</a></li>
                            @endfor
                            <li class="{{ $rightIconClass }}"><a href="{{ $rightIconUrl  }}"><i class="material-icons">chevron_right</i></a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection