<!DOCTYPE html>
<html>
<head>
    <meta charSet="utf-8" />
    <title>@yield('pageTitle') | {{ $site_title }}</title>
    <meta name="viewport" content="width=device-width" />
    <style>
        body { margin: 0 }
        .wrapper {
            color:#000;
            background:#fff;
            font-family:-apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            height:100vh;
            text-align:center;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
        }
        .heading {
            display:inline-block;
            border-right:1px solid rgba(0, 0, 0,.3);
            margin:0;
            margin-right:20px;
            padding:10px 23px 10px 0;
            font-size:24px;
            font-weight:500;
            vertical-align:top;
        }
        .item {
            display:inline-block;
            text-align:left;
            line-height:49px;
            height:49px;
            vertical-align:middle;
        }
        .normal-text {
            font-size:14px;
            font-weight:normal;
            line-height:inherit;
            margin:0;
            padding:0;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div>
@yield('error_content')
    </div>
</div>
</body>
</html>