@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Dashboard Siswa</h2>

    {{-- Alert sukses --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- LEFT PROFILE CARD --}}
        <div class="bg-white shadow-lg rounded-xl p-4">
            <div class="flex flex-col items-center">
                <img src="{{ foto_url($student->profile->foto) ?? asset('images/default-avatar.png') }}"
                     alt="Foto Profil"
                     class="w-40 h-52 object-cover rounded-lg shadow">

                <h3 class="text-xl font-semibold mt-4">{{ $student->name }}</h3>

                <div class="mt-3">
                    <span class="px-3 py-1 rounded-full text-sm
                        {{ $student->siswaProfile->status == 'aktif' ? 'text-green-700' : 'text-red-700' }}">
                        <i class="fa-solid fa-circle-check"></i> {{ ucfirst($student->profile->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- MAIN DETAILS --}}
        <div class="md:col-span-2 bg-white shadow-lg rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Informasi Siswa</h3>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div><strong>NISS:</strong> {{ $student->profile->niss ?? '-' }}</div>
                <div><strong>Nomor Jersey:</strong> {{ $student->profile->nomor_jersey ?? '-' }}</div>

                <div><strong>Jenis Kelamin:</strong> {{ ucfirst($student->profile->jenis_kelamin) }}</div>
                <div><strong>Tempat Lahir:</strong> {{ $student->profile->tempat_lahir }}</div>
                <div><strong>Tanggal Lahir:</strong> {{ $student->profile->tanggal_lahir }}</div>

                <div><strong>Kategori Umur:</strong> {{ $student->profile->kelompok_umur }} {{ $student->profile->jenis_kelamin_label }}</div>
                <div><strong>Tinggi Badan:</strong> {{ $student->profile->tinggi_badan ?? '-' }} cm</div>
                <div><strong>Berat Badan:</strong> {{ $student->profile->berat_badan ?? '-' }} kg</div>
            </div>

            <hr class="my-4">

            <h3 class="text-lg font-semibold mb-2 text-gray-700">Informasi Orang Tua</h3>
            @foreach($student->parents as $parent)
                <div class="mb-2 text-sm">
                    <p class="mb-1"><strong>Nama Ayah:</strong> {{ $parent->profile->nama_ayah }}</p>
                    <p class="mb-1"><strong>Nama Ibu:</strong> {{ $parent->profile->nama_ibu }}</p>
                    <p class="mb-1"><strong>Telepon:</strong> {{ $parent->userProfile->phone ?? '-' }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Documents & Jadwal Latihan--}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white shadow-lg rounded-xl px-4 pb-4 mt-6">
            @include('siswa.partials.documents', ['student' => $student])
        </div>

        <div class="bg-white shadow-lg rounded-xl px-4 py-4 pb-4 mt-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Jadwal Latihan Bulan {{ now()->format('F Y') }}</h2>

                @if($jadwalLatihan)
                    <div class="prose max-w-none mb-6">
                        {!! $jadwalLatihan->content !!}
                    </div>
                @else
                    <p class="text-gray-500 text-center italic mb-6">
                        Belum ada jadwal diinformasikan
                    </p>
                @endif
        </div>
    </div>

</div>
@endsection
