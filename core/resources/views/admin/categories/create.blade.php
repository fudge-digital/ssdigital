@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Tambah Kategori Baru</h1>

    <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="font-semibold">Nama Kategori</label>
            <input type="text" name="name" required class="w-full mt-1 p-3 border rounded-lg"
                   placeholder="Contoh: Pertandingan, Informasi, Pengumuman">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <button class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded-lg font-semibold">
            Simpan
        </button>
    </form>
</div>
@endsection
