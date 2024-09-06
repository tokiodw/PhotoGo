<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>タイトル</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Scripts and Styles -->
        @vite(['resources/js/app.jsx', 'resources/sass/app.scss'])
    </head>
    <body>
        @include('layouts.header')
        @yield('content')
    </body>
</html>
