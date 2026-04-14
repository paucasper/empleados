<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
        <body class="font-sans text-gray-900 antialiased">
            <div class="min-h-screen relative flex items-center justify-center
                        bg-cover bg-center bg-no-repeat">

                <div class="absolute inset-0 bg-slate-300/80"></div>

                <div class="relative w-full sm:max-w-md px-6 py-6
                            bg-white/90 backdrop-blur-md
                            shadow-xl overflow-hidden rounded-lg">
                    {{ $slot }}
                </div>
            </div>
        </body>

</html>
