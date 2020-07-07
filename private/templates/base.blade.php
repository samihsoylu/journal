<!DOCTYPE html>
<html lang="en">
    <head>
        <title>@yield('pageTitle') | {{ $site_title }}</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="{{ $assets_url }}/css/materialize/materialize.min.css" rel="stylesheet" />
        <link href="{{ $assets_url }}/css/style.css" rel="stylesheet" />
    </head>
    <body>
        <main>
@yield('content')
        </main>

        <script src="{{ $assets_url }}/js/jquery.js"></script>
        <script src="{{ $assets_url }}/js/materialize.min.js"></script>
        <script src="{{ $assets_url }}/js/script.js"></script>
    </body>
</html>
