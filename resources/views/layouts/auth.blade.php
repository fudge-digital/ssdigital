<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masuk')</title>

    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen w-full flex bg-gray-100 font-barlow">
    {{-- Kolom kiri --}}
    <div class="hidden md:flex w-1/2 bg-green-800 text-white flex-col justify-between items-center py-10 px-6 relative">
        
        {{-- Logo di atas --}}
        <div class="invisible w-full flex justify-center">
            <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" 
                alt="Logo KlubMan" 
                class="w-40 md:w-48 object-contain">
        </div>

        {{-- Teks tengah --}}
        <div class="text-center max-w-md">
            <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" 
                alt="Logo KlubMan" 
                class="w-40 md:w-48 object-contain text-center mx-auto">
            <h2 class="text-2xl font-bold leading-tight">SATRIA SILIWANGI BASKETBALL</h2>
            <p class="text-md text-white opacity-90 uppercase">
                Club Management System
            </p>
        </div>

        {{-- Gambar dekoratif bawah --}}
        <div class="text-center max-w-md mt-10">
            <p class="text-xs text-gray-100 opacity-90">
                SS DIGITAL V.2.0 &copy; 2025 // by. Fudge Digital Studio 
            </p>
        </div>

    </div>

    {{-- Kolom kanan (form area) --}}
    <div class="w-full md:w-1/2 bg-gray-50 flex flex-col overflow-y-auto">
        <div class="flex-grow flex items-center justify-center p-6 md:p-10">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow p-8 md:p-10 overflow-y-auto max-h-[90vh]">
                <h2 class="text-2xl font-semibold text-center mb-6">@yield('title')</h2>
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
