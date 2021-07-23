<header>
    <!-- Account menu dropdown (desktop) -->
    <ul id="account_menu" class="dropdown-content">
        <li><a href="{{ $account_url }}">Account settings</a></li>
        <li class="divider"></li>
        <li><a href="{{ $logout_url }}">Logout</a></li>
    </ul>

    <!-- Navigation bar (mobile -->
    <ul id="mobile_menu" class="sidenav">
        <li><a href="{{ $dashboard_url }}">Dashboard</a></li>
        <li><a href="{{ $entries_url }}">Entries</a></li>
        <li><a href="{{ $templates_url }}">Templates</a></li>
        <li><a href="{{ $categories_url }}">Categories</a></li>
        @if ($session->userHasAdminPrivileges())
            <li><a href="{{ $users_url }}">Users</a></li>
        @endif
        <li><a href="{{ $account_url }}">Account settings</a></li>
        <li><a href="{{ $logout_url }}">Logout</a></li>
    </ul>

    <!-- Entries menu dropdown (desktop) -->
    <ul id="entries_menu" class="dropdown-content">
        <li><a href="{{ $entries_url }}">View all entries</a></li>
        <li><a href="{{ $create_entry_url }}">Create a entry</a></li>
    </ul>

    <!-- Categories menu dropdown (desktop) -->
    <ul id="categories_menu" class="dropdown-content">
        <li><a href="{{ $categories_url }}">View all categories</a></li>
        <li><a href="{{ $create_category_url }}">Create a category</a></li>
    </ul>

    <!-- Templates menu dropdown (desktop) -->
    <ul id="templates_menu" class="dropdown-content">
        <li><a href="{{ $templates_url }}">View all templates</a></li>
        <li><a href="{{ $create_template_url }}">Create a template</a></li>
    </ul>

    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper">
                <a href="{{ $dashboard_url }}" class="brand-logo">{{ $site_title }}</a>
                <a href="#" data-target="mobile_menu" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li @if ($active_page === 'dashboard') class="active" @endif><a href="{{ $dashboard_url }}">Dashboard</a></li>
                    <li @if ($active_page === 'entries') class="active" @endif><a class="dropdown-trigger" href="#!" data-target="entries_menu">Entries<i class="material-icons right no-margin">arrow_drop_down</i></a></li>
                    <li @if ($active_page === 'templates') class="active" @endif><a class="dropdown-trigger" href="#!" data-target="templates_menu">Templates<i class="material-icons right no-margin">arrow_drop_down</i></a></li>
                    <li @if ($active_page === 'categories') class="active" @endif><a class="dropdown-trigger" href="#!" data-target="categories_menu">Categories<i class="material-icons right no-margin">arrow_drop_down</i></a></li>
                    @if ($session->userHasAdminPrivileges())
                        <li @if ($active_page === 'users') class="active" @endif><a href="{{ $users_url }}">Users</a></li>
                    @endif
                    <li><a class="dropdown-trigger" href="#!" data-target="account_menu"><i class="material-icons right">account_box arrow_drop_down</i></a></li>
                </ul>
            </div>
        </nav>
    </div>

    <div class="parallax-container parallax-page-heading">
        <div class="parallax"><img style="height:225px;" src="{{ $assets_url }}/images/header/parallax-photo.jpg"></div>
        <div class="container">
            <h2 class="white-text text-shadow vertical-center">@yield('pageTitle')</h2>
        </div>
    </div>
</header>
@include('components/alerts')