@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-8">📊 Dashboard Admin</h1>

    {{-- GRID STATISTIK --}}
    <div class="grid md:grid-cols-4 gap-6 mb-10">
        {{-- TOTAL SISWA --}}
        <div class="bg-white shadow rounded-2xl p-6 border border-gray-100">
            <h2 class="text-gray-600 text-sm mb-1">Total Siswa</h2>
            <p class="text-3xl font-bold text-gray-900">{{ $totalSiswa }}</p>
        </div>

        <div class="bg-green-50 shadow rounded-2xl p-6 border border-green-100">
            <h2 class="text-green-700 text-sm mb-1">Siswa Aktif</h2>
            <p class="text-3xl font-bold text-green-800">{{ $aktif }}</p>
        </div>

        <div class="bg-yellow-50 shadow rounded-2xl p-6 border border-yellow-100">
            <h2 class="text-yellow-700 text-sm mb-1">Belum Aktif</h2>
            <p class="text-3xl font-bold text-yellow-800">{{ $tidakAktif }}</p>
        </div>

        <div class="bg-red-50 shadow rounded-2xl p-6 border border-red-100">
            <h2 class="text-red-700 text-sm mb-1">Suspended</h2>
            <p class="text-3xl font-bold text-red-800">{{ $suspended }}</p>
        </div>
    </div>

    {{-- DAFTAR SISWA TERBARU --}}
    <h2 class="text-xl font-semibold text-gray-800 mb-10 mb-4">🧑‍🎓 Siswa Terbaru</h2>

    <div class="bg-white shadow rounded-2xl border border-gray-100">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kategori Umur</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentStudents as $s)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $s->user->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $s->kelompok_umur ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs
                                {{ $s->status === 'aktif' ? 'bg-green-100 text-green-700' :
                                ($s->status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($s->status_label) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $s->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                            Belum ada siswa baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5 text-right">
        <a href="{{ route('admin.pembayaran.index') }}"
        class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Daftar Pembayaran
        </a>
    </div>

    {{-- PEMBAYARAN --}}
    <h2 class="text-xl font-semibold text-gray-800 mt-10 mb-4">💰 Status Pembayaran Pendaftaran</h2>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-2xl p-6 border border-gray-100">
            <h2 class="text-gray-600 text-sm mb-1">Total Pembayaran</h2>
            <p class="text-3xl font-bold text-gray-900">{{ $totalPembayaran }}</p>
        </div>

        <div class="bg-yellow-50 shadow rounded-2xl p-6 border border-yellow-100">
            <h2 class="text-yellow-700 text-sm mb-1">Pending</h2>
            <p class="text-3xl font-bold text-yellow-800">{{ $pendingPembayaran }}</p>
        </div>

        <div class="bg-green-50 shadow rounded-2xl p-6 border border-green-100">
            <h2 class="text-green-700 text-sm mb-1">Verified</h2>
            <p class="text-3xl font-bold text-green-800">{{ $verifiedPembayaran }}</p>
        </div>
    </div>

</div>
@endsection
