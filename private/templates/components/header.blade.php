<header>
    <!-- Account menu dropdown (desktop) -->
    <ul id="account_menu" class="dropdown-content">
        <li><a href="#!">Profile</a></li>
        <li class="divider"></li>
        <li><a href="{{ $logout_url }}">Logout</a></li>

    </ul>
    <!-- Create menu dropdown (desktop) -->
    <ul id="create_menu" class="dropdown-content">
        <li><a href="#!">Entry</a></li>
        <li><a href="#!">Category</a></li>
    </ul>

    <ul id="mobile_menu" class="sidenav">
        <li><a href="#!">Dashboard</a></li>
        <li><a href="#!">Entries</a></li>
        <li><a href="#!">Categories</a></li>
        <li><a href="#!">Users</a></li>
        <li><a href="#!">Profile</a></li>
        <li><a href="{{ $logout_url }}">Logout</a></li>
    </ul>

    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper">
                <a href="#!" class="brand-logo">Journal</a>
                <a href="#" data-target="mobile_menu" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li><a href="dashboard.html">Dashboard</a></li>
                    <li><a href="#!">Entries</a></li>
                    <li><a href="categories-view.html">Categories</a></li>
                    <li><a href="#!">Users</a></li>
                    <li><a class="dropdown-trigger" href="#!" data-target="account_menu"><i class="material-icons right">account_box arrow_drop_down</i></a></li>
                </ul>
            </div>
        </nav>
    </div>

    <div class="parallax-container parallax-page-heading">
        <div class="parallax"><img src="{{ $assets_url }}/images/title-images/photo-of-island-during-golden-hour-1119973.jpg"></div>
        <div class="container">
            <h2 class="white-text text-shadow vertical-center">@yield('pageTitle')</h2>
        </div>
    </div>
</header>