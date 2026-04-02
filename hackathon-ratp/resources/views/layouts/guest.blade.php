<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RATP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-100 min-h-screen">

        {{-- Header RATP --}}
        <header class="bg-[#004fa3]">
            <div class="max-w-md mx-auto px-4 py-4 flex items-center justify-center">
                <a href="/">
                    <img src="{{ asset('images/Image1 (1).png') }}" alt="RATP Réseaux de Surface" class="h-16 w-auto" />
                </a>
            </div>
            <div class="h-1 bg-[#4bc0ad]"></div>
        </header>

        <div class="flex flex-col items-center pt-8 pb-12 px-4">
            <div class="w-full sm:max-w-md bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>

    </body>
</html>
