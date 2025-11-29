@extends('layouts.app')

@section('content')
@php $user = auth()->user(); @endphp

<div class="container mx-auto p-4 max-w-4xl">
    <h2 class="text-xl font-bold mb-4">Dokumen Siswa — {{ $siswa->nama ?? ($siswa->user->name ?? 'Siswa') }}</h2>

    {{-- KK --}}
    <div class="bg-white p-4 rounded shadow mb-4">
        <h3 class="font-semibold">Kartu Keluarga (KK)</h3>
        @php $kk = $siswa->documents->firstWhere('type','kk'); @endphp

        @if($kk)
            <div class="flex items-center gap-4 mt-2">
                <a href="{{ route('siswa.documents.preview', $kk->id) }}" target="_blank" class="text-blue-600 underline">Lihat KK</a>
                @can('update', $kk)
                    <form action="{{ route('siswa.documents.update', $kk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" required>
                        <button class="px-3 py-1 bg-yellow-500 text-white rounded">Replace</button>
                    </form>
                @endcan
                @can('delete', $kk)
                    <form action="{{ route('siswa.documents.destroy', $kk->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1 bg-red-600 text-white rounded">Hapus</button>
                    </form>
                @endcan
            </div>
        @else
            @can('create', \App\Models\StudentDocument::class)
            <form action="{{ route('siswa.documents.store', $siswa->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <input type="hidden" name="type" value="kk">
                <input type="file" name="file" required>
                <button class="px-3 py-1 bg-green-600 text-white rounded">Upload KK</button>
            </form>
            @else
                <p class="text-sm text-gray-500 mt-2">Belum ada KK.</p>
            @endcan
        @endif
    </div>

    {{-- Akta --}}
    <div class="bg-white p-4 rounded shadow mb-4">
        <h3 class="font-semibold">Akta Anak</h3>
        @php $akta = $siswa->documents->firstWhere('type','akta'); @endphp

        @if($akta)
            <div class="flex items-center gap-4 mt-2">
                <a href="{{ route('siswa.documents.preview', $akta->id) }}" target="_blank" class="text-blue-600 underline">Lihat Akta</a>
                @can('update', $akta)
                    <form action="{{ route('siswa.documents.update', $akta->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" required>
                        <button class="px-3 py-1 bg-yellow-500 text-white rounded">Replace</button>
                    </form>
                @endcan
                @can('delete', $akta)
                    <form action="{{ route('siswa.documents.destroy', $akta->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1 bg-red-600 text-white rounded">Hapus</button>
                    </form>
                @endcan
            </div>
        @else
            @can('create', \App\Models\StudentDocument::class)
            <form action="{{ route('siswa.documents.store', $siswa->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <input type="hidden" name="type" value="akta">
                <input type="file" name="file" required>
                <button class="px-3 py-1 bg-green-600 text-white rounded">Upload Akta</button>
            </form>
            @else
                <p class="text-sm text-gray-500 mt-2">Belum ada Akta.</p>
            @endcan
        @endif
    </div>

    {{-- Dokumen Lain (repeater) --}}
    <div class="bg-white p-4 rounded shadow mb-4">
        <h3 class="font-semibold">Dokumen Lain</h3>

        {{-- daftar dokumen lain --}}
        @foreach($siswa->documents->where('type','lain') as $doc)
            <div class="flex items-center justify-between gap-4 mt-2 border-b py-2">
                <div>
                    <div class="font-medium">{{ $doc->title ?? 'Dokumen Lain' }}</div>
                    <a href="{{ route('siswa.documents.preview', $doc->id) }}" target="_blank" class="text-blue-600 underline text-sm">Lihat / Download</a>
                </div>

                <div class="flex items-center gap-2">
                    @can('update', $doc)
                        <form action="{{ route('siswa.documents.update', $doc->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="title" value="{{ $doc->title }}" placeholder="Judul" class="border px-2 py-1">
                            <input type="file" name="file" class="border px-2 py-1">
                            <button class="px-2 py-1 bg-yellow-500 text-white rounded">Save</button>
                        </form>
                    @endcan

                    @can('delete', $doc)
                        <form action="{{ route('siswa.documents.destroy', $doc->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="px-2 py-1 bg-red-600 text-white rounded">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach

        {{-- form tambah dokumen lain (parent/admin) --}}
        @can('create', \App\Models\StudentDocument::class)
            <div class="mt-4">
                <h4 class="font-medium">Tambah Dokumen Lain</h4>
                <form id="addLainForm" action="{{ route('siswa.documents.store', $siswa->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                    @csrf
                    <input type="hidden" name="type" value="lain">
                    <div id="lainWrapper">
                        <div class="flex gap-2 items-center mb-2">
                            <input type="text" name="title[]" placeholder="Judul dokumen" class="border px-2 py-1 w-1/2" required>
                            <input type="file" name="file[]" class="border px-2 py-1" required>
                            <button type="button" class="removeBtn px-2 py-1 bg-red-600 text-white rounded hidden">Hapus</button>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-2">
                        <button id="addLainBtn" type="button" class="px-3 py-1 bg-blue-600 text-white rounded">Tambah Dokumen Lain</button>
                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded">Upload Semua</button>
                    </div>
                </form>
            </div>
        @endcan
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('addLainBtn');
    const wrapper = document.getElementById('lainWrapper');

    addBtn.addEventListener('click', function(){
        const node = document.createElement('div');
        node.classList.add('flex','gap-2','items-center','mb-2');

        node.innerHTML = `
            <input type="text" name="title[]" placeholder="Judul dokumen" class="border px-2 py-1 w-1/2" required>
            <input type="file" name="file[]" class="border px-2 py-1" required>
            <button type="button" class="removeBtn px-2 py-1 bg-red-600 text-white rounded">Hapus</button>
        `;
        wrapper.appendChild(node);

        node.querySelector('.removeBtn').addEventListener('click', function(){
            node.remove();
        });
    });
});
</script>
@endpush
