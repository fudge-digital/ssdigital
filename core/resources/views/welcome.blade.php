<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Satria Siliwangi CMS')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

    <body class="relative min-h-screen flex flex-col items-center justify-center bg-green-700 text-white overflow-hidden px-6 py-8">

        <!-- Background Layer TANPA TAILWIND ARBITRARY -->
        <div 
            class="absolute inset-0 bg-cover bg-center opacity-10 -z-10"
            style="background-image: url('{{ asset('images/contour.png') }}');">
        </div>

        <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" class="w-64 mb-6">

        <a href="{{ route('login') }}" 
        class="bg-white text-green-700 px-4 py-1 rounded-sm text-xs font-semibold uppercase">
            Login Disini
        </a>

        <h2 class="text-sm font-semibold uppercase text-white mt-6 mb-1">
            Club Management System
        </h2>

        <p class="text-xs text-white text-center">
            &copy; 2025 SATRIA SILIWANGI<br>SS Digital V.2.0
        </p>
    </body>
</html>
