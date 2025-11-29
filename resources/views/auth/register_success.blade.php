@extends('layouts.main')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen">
    <div class="p-8 bg-green-900 rounded-2xl shadow-lg text-center text-white">
        <h1 class="text-3xl font-bold mb-4">Pendaftaran Berhasil</h1>
        <p class="text-lg mb-6">Silakan cek email Anda untuk detail login orang tua dan siswa.</p>
        <a href="{{ route('login') }}" class="px-6 py-2 bg-white text-green-800 rounded-lg font-semibold hover:bg-gray-200">
            Kembali ke Login
        </a>
    </div>
    <div class="container mx-auto text-center text-xs mt-3 text-green-900">
        &copy; 2025 SS DIGITAL V.2.0 // by. Fudge Digital Studio
    </div>
</div>
@endsection
