<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Cipi') }} | @yield('title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://allyoucan.cloud/cdn/fontawesome/5.11.2/css/all.css">
    <link rel='stylesheet' href='https://allyoucan.cloud/fonts/css/?family=Nunito-Regular'>
    <link href="/app.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        @yield('content')
    </div>

    <script src="https://allyoucan.cloud/cdn/jquery/core/3.4.1/jquery.js"></script>
    <script src="https://allyoucan.cloud/cdn/bootstrap/core/4.2.1/js/bootstrap.js"></script>
    <script src="https://allyoucan.cloud/cdn/webshim/1.16.0/polyfiller.js"></script>
    <script src="/app.js"></script>

</body>

</html>
