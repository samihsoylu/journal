<header>
    <!-- Account menu dropdown (desktop) -->
    <ul id="account_menu" class="dropdown-content">
        <li><a href="{{ $account_url }}">Account</a></li>
        <li class="divider"></li>
        <li><a href="{{ $logout_url }}">Logout</a></li>

    </ul>

    <ul id="mobile_menu" class="sidenav">
        <li><a href="{{ $dashboard_url }}">Dashboard</a></li>
        <li><a href="{{ $entries_url }}">Entries</a></li>
        <li><a href="{{ $templates_url }}">Templates</a></li>
        <li><a href="{{ $categories_url }}">Categories</a></li>
        @if ($session->userHasAdminPrivileges())
            <li><a href="{{ $users_url }}">Users</a></li>
        @endif
        <li><a href="{{ $account_url }}">Account</a></li>
        <li><a href="{{ $logout_url }}">Logout</a></li>
    </ul>

    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper">
                <a href="{{ $dashboard_url }}" class="brand-logo">{{ $site_title }}</a>
                <a href="#" data-target="mobile_menu" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li @if ($active_page === 'dashboard') class="active" @endif><a href="{{ $dashboard_url }}">Dashboard</a></li>
                    <li @if ($active_page === 'entries') class="active" @endif><a href="{{ $entries_url }}">Entries</a></li>
                    <li @if ($active_page === 'templates') class="active" @endif><a href="{{ $templates_url }}">Templates</a></li>
                    <li @if ($active_page === 'categories') class="active" @endif><a href="{{ $categories_url }}">Categories</a></li>
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