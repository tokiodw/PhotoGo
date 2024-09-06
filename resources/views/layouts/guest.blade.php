<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Login - SB Admin</title>
        
        @vite(['resources/js/app.ts', 'resources/js/bootstrap.js', 'resources/sass/app.scss'])
    </head>
    <body id="guest-body">
        <div class="d-flex vh-100 justify-content-center align-items-center">
            <div class="flex-grow-1">
                <main>
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-7 col-lg-5 col-12 d-flex align-items-center justify-content-lg-start justify-content-center ps-lg-5 mb-lg-0 mb-5">
                                <x-application-logo></x-application-logo>
                            </div>
                            <div class="col-xl-5 col-lg-7 col-12">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
