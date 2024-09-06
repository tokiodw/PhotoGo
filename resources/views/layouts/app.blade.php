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
<body>
    {{ $components }}
    <div class="vh-100">
        <header>
            <x-header></x-header>
        </header>
        <main class="d-flex justify-content-center">
            <div class="flex-grow-1">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>