<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PhotoGo</title>
    @vite(['resources/js/bootstrap.js', 'resources/sass/app.scss', 'resources/js/app.ts'])
    {{ $scripts }}
</head>

<body class="d-flex justify-content-center flex-column vh-100">
    {{ $components }}
    <header>
        <x-header></x-header>
    </header>
    <main class="flex-grow-1">
        {{ $slot }}
    </main>
</body>

</html>
