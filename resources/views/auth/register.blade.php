@extends('layouts.auth') {{-- layout 2 kolom yang kamu buat --}}
@section('title', 'Registrasi Satria Siliwangi Basketball')

@push('styles')
<style>
#siswa-container::-webkit-scrollbar {
    width: 6px;
}
#siswa-container::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 6px;
}
#siswa-container {
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.2) transparent;
}
</style>
@endpush

@section('content')
<form action="{{ route('register.store') }}" method="POST" class="space-y-6">
    @csrf

    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Data Orang Tua</h3>
    <div class="bg-gray-100 rounded-2xl p-6 border mb-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Nama Ayah</label>
                <input type="text" name="nama_ayah" class="w-full border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="text-sm font-medium">Nama Ibu</label>
                <input type="text" name="nama_ibu" class="w-full border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" class="w-full border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="text-sm font-medium">No. WhatsApp</label>
                <input type="text" name="phone" class="w-full border-gray-300 rounded-lg" required>
            </div>
            <div class="col-span-2">
                <label class="text-sm font-medium">Alamat</label>
                <textarea name="alamat" class="w-full border-gray-300 rounded-lg" rows="3" required></textarea>
            </div>
        </div>
    </div>

    <div id="siswa-container" class="space-y-6 overflow-y-auto max-h-[60vh] pr-2">
        <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Data Siswa</h3>
        @include('components.siswa-form', ['index' => 0])
    </div>

    <button type="button" id="add-siswa"
        class="bg-green-700 hover:bg-green-800 text-white rounded-xl px-4 py-2">+ Tambah Siswa</button>

    <div class="mt-6">
        <button type="submit"
            class="w-full bg-green-700 hover:bg-green-800 text-white py-3 rounded-xl font-semibold">Daftar Sekarang</button>
    </div>
</form>
@endsection
