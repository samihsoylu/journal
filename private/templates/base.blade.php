<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>@yield('pageTitle') | Notes</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="{{ $assets_url }}/css/primer.css" rel="stylesheet" />
        <link href="{{ $assets_url }}/css/custom.css" rel="stylesheet" />
    </head>
    <body>

    @yield('content')
    </body>
</html>
