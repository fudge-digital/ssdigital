@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h1 class="text-xl font-bold mb-4">Daftar Siswa</h1>

    {{-- Pencarian --}}
    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}"
            class="border rounded p-2 w-64"
            placeholder="Cari nama / NIS / no jersey">
        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Cari
        </button>
    </form>

    <div class="bg-white shadow rounded-lg">

        <table class="table-auto w-full text-left border-collapse">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-3">Foto</th>
                    <th class="p-3">NISS</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Kategori</th>
                    <th class="p-3">No Jersey</th>
                    <th class="p-3">No WA</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($student as $item)
                <tr x-data="{ open: false }" @click.outside="open = false" class="border-b hover:bg-gray-50 realtive">

                    <td class="p-3">
                        <img src="{{ foto_url($item->profile->foto, 'siswa') ?? 'https://placehold.co/100x100?text=foto+siswa' }}"
                            class="w-20 h-20 object-cover rounded-full">
                    </td>

                    <td class="p-3 font-medium">{{ $item->siswaProfile?->niss ?? '-' }}</td>
                    <td class="p-3 font-medium">{{ $item->name }}</td>
                    <td class="p-3">
                        <div class="px-2 py-1 rounded w-24 text-center text-xs {{ $item->siswaProfile->jenis_kelamin_label === 'Putra' ? 'bg-blue-600 font-medium text-white' : 'bg-pink-300 text-white font-medium' }}">
                            <span class="font-medium">{{ $item->siswaProfile?->kelompok_umur ?? '-' }}</span>
                            @if($item->siswaProfile?->jenis_kelamin_label)
                                <span class="">
                                    {{ $item->siswaProfile->jenis_kelamin_label }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="p-3">{{ $item->siswaProfile?->nomor_jersey ?? '-' }}</td>
                    <td class="p-3">{{ $item->siswaProfile?->no_whatsapp ?? '-' }}</td>

                    <td class="p-3">
                        <span class="px-3 py-1 rounded text-xs {{ $item->siswaProfile?->status === 'aktif' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                            {{ $item->siswaProfile?->status_label ?? '-' }}
                        </span>
                    </td>

                    <td class="p-3 text-center relative">
                        <button 
                            @click="open = !open"
                            class="px-2 py-2 rounded-md hover:bg-gray-200 transition">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>

                        <div 
                        x-show="open"
                        x-transition.opacity.scale.origin.top.right
                        x-cloak
                        class="absolute right-0 mt-1 w-40 bg-white rounded-md shadow-lg border z-50 text-left p-2">
                            <a href="{{ route('siswa.edit', $item->id) }}" 
                                class="flex items-center gap-2 px-4 py-2 hover:bg-green-600 hover:text-white rounded text-sm transition">
                                <i class="fa-regular fa-pen-to-square mr-2"></i> Edit
                            </a>

                            <a href="{{ route('siswa.show', $item->id) }}" 
                                class="flex items-center gap-2 px-4 py-2 hover:bg-blue-600 hover:text-white rounded text-sm transition">
                                <i class="fa-regular fa-eye mr-2"></i> Lihat
                            </a>

                            <a href="#"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-red-600 hover:text-white rounded text-sm transition">
                                <i class="fa-solid fa-ban mr-2"></i> Tangguhkan
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-4 text-gray-500">
                        Tidak ada data siswa ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <div class="mt-4">
        {{ $student->links() }}
    </div>
</div>
@endsection
