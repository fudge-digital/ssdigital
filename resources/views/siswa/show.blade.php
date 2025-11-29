@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">

    <div class="mb-6 flex justify-between content-center items-center">
        <div>
            <h1 class="text-2xl font-bold">{{ $student->name }}</h1>
        </div>
        <div>
            <p class="text-sm">
                <a href="{{ backRoute() }}" class="flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
                </a>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-6 shadow rounded-lg">
            <div class="flex flex-row mb-4">
                <div class="basis-1/3">
                    {{-- Foto --}}
                    <div class="text-center text-sm">
                        <img src="{{ foto_url($student->profile->foto, 'siswa') ?? 'https://placehold.co/300x400' }}"
                        class="w-48 h-64 object-cover rounded-md mb-4 mx-auto">
                        <p class="text-center text-sm">NISS: {{ $student->profile->niss ?? 'N/A' }}</p>
                    </div> 
                </div>

                <div class="basis-2/3">
                    {{-- Detail Right --}}
                    <h2 class="text-2xl font-bold"><i class="fa-solid fa-shirt mr-2"></i>{{ $student->profile?->nomor_jersey ?? '0' }} - {{ $student->profile->kelompok_umur }} {{ $student->profile->jenis_kelamin_label }}</h2>
                    <div class="grid grid-cols-3 gap-4 mt-4 mb-4 border-b pb-4">
                        <div>
                            <h2 class="text-gray-500 text-sm">Status</h2>
                            <p class="text-gray-700 font-medium text-left">
                                @if($student->profile->status === 'aktif')
                                    <span class="rounded text-blue-500 text-sm"><i class="fa-solid fa-circle-check mr-1"></i>{{ $student->profile->status_label }}</span>
                                @elseif($student->profile->status === 'suspended')
                                    <span class="rounded text-red-500 text-sm"><i class="fa-solid fa-circle-minus mr-1"></i>{{ $student->profile->status_label }}</span>
                                @elseif($student->profile->status === 'tidak_aktif')
                                    <span class="rounded text-yellow-500 text-sm"><i class="fa-solid fa-circle-xmark mr-1"></i>{{ $student->profile->status_label }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h2 class="text-gray-500 text-sm">Nama Panggilan</h2>
                            <p class="text-black font-medium text-sm">{{ $student->profile->nama_panggilan ?? '-' }}</p>
                        </div>
                        <div>
                            <h2 class="text-gray-500 text-sm">No WhatsApp</h2>
                            @if($student->profile->no_whatsapp)
                            <p>
                                <a href="https://wa.me/{{ formatPhone($student->profile->no_whatsapp) ?? '0' }}" target="_blank" class="p-1 rounded text-white text-xs bg-green-500 font-medium hover:bg-green-200 hover:text-green-900 transition"><i class="fa-brands fa-whatsapp mr-2"></i>{{ $student->profile->no_whatsapp }}</a>
                            </p>
                            @else
                            <p class="text-yellow-500 font-medium text-sm mt-1"><i class="fa-solid fa-circle-minus mr-2"></i>Belum Ada</p>
                            @endif
                        </div>
                    </div>
                    <!-- <h3 class="text-lg font-semibold mb-2">Informasi Orang Tua</h3> -->
                    @forelse ($student->parents as $parent)
                    <div class="space-y-4 text-sm">
                        <div class="flex flex-row justify-between items-center">
                            <div>
                                <span class="font-medium">Nama Ayah:</span> {{ $parent->profile->nama_ayah ?? '-' }}
                            </div>
                            <div>
                                <a href="#" class="px-2 py-1 capitalize rounded bg-gray-200 text-black-500 hover:bg-gray-500 hover:text-white transition"><i class="fa-solid fa-eye mr-1"></i>lihat</a>
                            </div>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <div>
                                <span class="font-medium">Nama Ibu:</span> {{ $parent->profile->nama_ibu ?? '-' }}
                            </div>
                            <div>
                                <a href="#" class="px-2 py-1 capitalize rounded bg-gray-200 text-black-500 hover:bg-gray-500 hover:text-white transition"><i class="fa-solid fa-eye mr-1"></i>lihat</a>
                            </div>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <div>
                                <span class="font-medium">Kontak Darurat:</span> {{ $parent->userProfile->phone ?? '-' }}
                            </div>
                            <div>
                                <a href="https://wa.me/{{formatPhone($parent->userProfile->phone) ?? '#'}}" target="_blank" class="px-2 py-1 capitalize rounded bg-green-500 text-white hover:bg-green-200 hover:text-green-800 transition"><i class="fa-brands fa-whatsapp mr-1"></i>WhatsApp</a>
                            </div>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <div>
                                <span class="font-medium">Alamat:</span> <p class="text-sm leading-8">{{ $parent->userProfile->alamat ?? 'Belum Update' }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500">Belum ada data orang tua terhubung.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="bg-white p-6 shadow rounded-lg">
            <h2 class="text-xl font-bold mb-4">Detail Informasi Siswa</h2>
            <table class="w-full text-left text-sm">
                <tr>
                    <th class="py-2 px-4 border-b">Tempat & Tanggal Lahir</th>
                    <td class="py-2 px-4 border-b">{{ $student->profile->tempat_lahir ?? '-' }}, {{ formatDate($student->profile->tanggal_lahir) }}</td>
                </tr>
                <tr>
                    <th class="py-2 px-4 border-b">Gender</th>
                    <td class="py-2 px-4 border-b">{{ $student->profile->jenis_kelamin ?? '-' }}</td>
                </tr>
                <tr>
                    <th class="py-2 px-4 border-b">Usia</th>
                    <td class="py-2 px-4 border-b">{{ $student->profile->usia ?? '-' }} Tahun</td>
                </tr>
                <tr>
                    <th class="py-2 px-4 border-b">Asal Sekolah</th>
                    <td class="py-2 px-4 border-b">{{ $student->profile->asal_sekolah ?? '-' }}</td>
                </tr>
                <tr>
                    <th class="py-2 px-4 border-b">Size Jersey</th>
                    <td class="py-2 px-4 border-b">Size {{ $student->profile->size_jersey ?? '-' }}</td>
                </tr>
            </table>
            {{-- Section View Dokumen untuk Semua Role --}}
            @include('siswa.partials.documents')
        </div>
    </div>

    <div class="flex justify-end">
        @if($student->profile && $student->profile->status === 'tidak_aktif')
        <a href="{{ route('siswa.edit', $student->id) }}" 
            class="inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition" cusror="not-allowed" disabled>
            <i class="fa-solid fa-pen-to-square mr-2"></i>Edit Profil Siswa
        </a>
        @else
        <a href="{{ route('siswa.edit', $student->id) }}" 
            class="inline-block px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
            <i class="fa-solid fa-pen-to-square mr-2"></i>Edit Profil Siswa
        </a>
        @endif
    </div>

    {{-- Section khusus untuk pelatih, admin --}}
    @if(auth()->user()->hasRole(['admin','pelatih','asisten_pelatih','manajer_tim']))
        {{-- @include('siswa.partials.absensi') --}}
        Absensi section untuk admin dan pelatih (sementara dihilangkan)
    @endif

    {{-- Section untuk orang tua --}}
    @if(auth()->user()->isOrangTua())
        @include('partials.iuran-summary')
    @endif

    {{-- Section untuk siswa sendiri --}}
    @if(auth()->user()->isSiswa() && auth()->id() == $student->id)
        {{-- @include('siswa.partials.myprofile') --}}
        Profil section untuk siswa sendiri (sementara dihilangkan)
    @endif

</div>
@endsection
