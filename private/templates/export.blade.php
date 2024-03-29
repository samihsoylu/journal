<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            width: 100%;
            max-width: 890px;
            margin: 0 auto;
        }

        h1 { font-size: 30px; font-weight:normal; }
        h2 { font-size: 24px; font-weight:normal; }

        h3, h4, h5, h6 { font-size: 16px; font-weight:bold; }

        h1, h2, h3, h4, h5, h6, p {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        blockquote {
            margin: 20px 0;
            padding-left: 1.5rem;
            border-left: 5px solid #4d6470;
        }

        /*
        * For rendering images inserted using the image plugin.
        * Includes image captions using the HTML5 figure element.
        /**!*/

        figure.image {
            display: inline-block;
            border: 1px solid gray;
            margin: 0 2px 0 1px;
            background: #f5f2f0;
        }

        figure.align-left,
        img.align-left {
            float: left;
            margin-right:15px;
        }

        figure.align-right,
        img.align-right {
            float: right;
            margin-left:15px;
        }

        figure.image img {
            margin: 8px 8px 0 8px;
        }

        figure.image figcaption {
            margin: 6px 8px 6px 8px;
            text-align: center;
        }

    </style>
</head>
<body>
    {!! $content !!}
</body>
</html>