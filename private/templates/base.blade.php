<!DOCTYPE html>
<html lang="en">
    <head>
        <title>@yield('pageTitle') | {{ $site_title }}</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="apple-touch-icon" sizes="57x57" href="{{ $assets_url }}/images/favicon/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ $assets_url }}/images/favicon/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ $assets_url }}/images/favicon/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ $assets_url }}/images/favicon/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ $assets_url }}/images/favicon/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ $assets_url }}/images/favicon/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ $assets_url }}/images/favicon/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ $assets_url }}/images/favicon/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $assets_url }}/images/favicon/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ $assets_url }}/images/favicon/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $assets_url }}/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ $assets_url }}/images/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ $assets_url }}/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="{{ $assets_url }}/images/favicon/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ $assets_url }}/images/favicon/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">

        <link href="{{ $assets_url }}/css/materialize/materialize.min.css" rel="stylesheet" />
        <link href="{{ $assets_url }}/css/jquery-ui/jquery-ui.css" rel="stylesheet" />
        <link href="{{ $assets_url }}/css/style.css" rel="stylesheet" />
    </head>
    <body>
        <main>
@yield('content')
        </main>

        <script src="{{ $assets_url }}/js/jquery.js"></script>
        <script src="{{ $assets_url }}/js/jquery-ui.min.js"></script>
        <script src="{{ $assets_url }}/js/jquery.ui.touch-punch.min.js"></script>
        <script src="{{ $assets_url }}/js/materialize.min.js"></script>
        <script type="text/javascript">
            const BASE_URL = '{{ $base_url }}';
        </script>
        <script src="{{ $assets_url }}/js/script.js"></script>
@yield('jquery-scripts')
    </body>
</html>
