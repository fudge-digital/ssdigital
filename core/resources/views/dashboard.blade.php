@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Utama')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700">Total Siswa Aktif</h3>
        <p class="text-3xl font-bold text-green-700 mt-2">124</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700">Total Pelatih</h3>
        <p class="text-3xl font-bold text-green-700 mt-2">8</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700">Pertandingan Terdekat</h3>
        <p class="text-3xl font-bold text-green-700 mt-2">3</p>
    </div>
</div>
@endsection
