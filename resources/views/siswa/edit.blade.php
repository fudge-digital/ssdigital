@extends('layouts.app')

@section('content')

@php
    $student = $profile ?? $user->siswaProfile ?? null;
    $canEdit = fn(string $field) => in_array('all', $editableFields) || in_array($field, $editableFields);
@endphp

<div class="container mx-auto py-6">
    <div class="mb-6 flex justify-between content-center items-center">
        <div>
            <h1 class="text-2xl font-bold">Edit Data Siswa — {{ $user->name }}</h1>
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
        <div class="bg-white rounded px-4 py-6">

            <form action="{{ route('siswa.update', $user->id) }}" method="POST">
                @csrf @method('PUT')

                @include('components.form-error-global')

                {{-- EMAIL --}}
                <div class="bg-white rounded-xl grid grid-cols-1 md:grid-cols-2 gap-6 border p-4 mb-6">
                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email"
                            value="{{ old('email', $user->email) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('user.email') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- PASSWORD --}}
                    @if($canEdit('user.password'))
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Password Baru</label>
                        <input type="password" name="password" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-600">* Kosongkan jika tidak ingin mengganti password</p>
                        @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    @endif
                </div>

                {{-- FOTO SISWA --}}
                <div class="grid grid-cols-2 gap-6 my-6">
                    <div>
                        <label class="font-bold mb-2 block">Foto Siswa (3x4)</label>

                        <input type="hidden" id="siswa_profile_id" value="{{ $profile->id }}">

                        <!-- Preview Thumbnail -->
                        <input type="file" id="photoInput" class="hidden" accept="image/*">
                        <input type="hidden" name="foto_base64" id="fotoCropped">
                        <img id="photoPreview"
                        src="{{ $profile->foto ? asset($profile->foto) : 'https://dummyimage.com/300x400/ccc/000&text=Foto+Siswa' }}"
                        class="w-32 h-44 object-cover rounded-md shadow cursor-pointer"
                        onclick="openPreviewModal()">

                        <div class="flex items-center gap-2 mt-2">
                            <button type="button"
                                onclick="document.getElementById('photoInput').click()"
                                class="px-3 py-1 bg-blue-600 text-white rounded text-sm">
                                Upload Foto
                            </button>

                            <button onclick="removePhoto()" type="button"
                                class="px-3 py-1 bg-red-600 text-white rounded text-sm">
                                Remove
                            </button>
                        </div>
                    </div>

                    <div id="previewModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center">
                        <img src="" id="previewImage" class="max-h-[80vh] rounded shadow">
                    </div>

                    <!-- Modal Cropper -->
                    <div id="cropperModal"
                        class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
                        <div class="bg-white p-4 rounded-lg w-[520px]">
                            <img id="cropperImage" class="max-h-[70vh] mx-auto rounded">

                            <div class="text-right mt-4 space-x-2">
                                <button onclick="closeCropperModal()" class="px-3 py-1 border rounded">Cancel</button>
                                <button type="button" id="cropButton" class="px-3 py-1 bg-blue-600 text-white rounded">
                                    Crop & Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DATA SISWA --}}
                <div class="bg-white border rounded-xl grid grid-cols-1 md:grid-cols-2 p-4 gap-6">
                    {{-- NISS --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">NISS</label>
                        <input type="text" name="niss"
                            value="{{ old('niss', $student?->niss) }}"
                            @class([
                            'w-full border rounded px-3 py-2',
                            'bg-gray-100 border border-gray-100 text-gray-400 cursor-not-allowed' => !$canEdit('profile.niss')
                        ])
                        {{ $canEdit('profile.niss') ? '' : 'readonly' }}>
                    </div>
                    {{-- NAMA PANGGILAN --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Panggilan</label>
                        <input type="text" name="nama_panggilan"
                            value="{{ old('nama_panggilan', $student?->nama_panggilan) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('profile.nama_panggilan') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- NO WHATSAPP --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">No WhatsApp</label>
                        <input type="text" name="no_whatsapp"
                            value="{{ old('no_whatsapp', $student?->no_whatsapp) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('profile.no_whatsapp') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- TEMPAT LAHIR --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir"
                            value="{{ old('tempat_lahir', $student?->tempat_lahir) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('profile.tempat_lahir') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- TANGGAL LAHIR --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir"
                            value="{{ old('tanggal_lahir', $student?->tanggal_lahir) }}"
                            @class([
                                    'w-full border rounded px-3 py-2',
                                    'bg-gray-100 border border-gray-100 text-gray-400 cursor-not-allowed' => !$canEdit('profile.tanggal_lahir'),
                            ])
                            {{ $canEdit('profile.tanggal_lahir') ? '' : 'readonly' }}>
                    </div>

                    {{-- ASAL SEKOLAH --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah"
                            value="{{ old('asal_sekolah', $student?->asal_sekolah) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('profile.asal_sekolah') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- SIZE --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Size Jersey</label>
                        <input type="text" name="size_jersey"
                            value="{{ old('size_jersey', $student?->size_jersey) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('profile.size_jersey') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- NOMOR JERSEY --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor Jersey</label>
                        <input type="text" name="nomor_jersey"
                            value="{{ old('nomor_jersey', $student?->nomor_jersey) }}"
                            @class([
                                'w-full border rounded px-3 py-2',
                                'bg-gray-100 border border-gray-100 text-gray-400 cursor-not-allowed' => !$canEdit('profile.nomor_jersey')
                            ])
                            {{ $canEdit('profile.nomor_jersey') ? '' : 'readonly' }}>
                    </div>

                    {{-- TINGGI --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Tinggi Badan (cm)</label>
                        <input type="number" name="tinggi_badan"
                            value="{{ old('tinggi_badan', $student?->tinggi_badan) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('profile.tinggi_badan') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- BERAT --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Berat Badan (kg)</label>
                        <input type="number" name="berat_badan"
                            value="{{ old('berat_badan', $student?->berat_badan) }}"
                            class="w-full border rounded px-3 py-2"
                            {{ $canEdit('profile.berat_badan') ? '' : 'readonly class=bg-gray-100 cursor-not-allowed' }}>
                    </div>

                    {{-- STATUS --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Status Siswa</label>
                        @if($canEdit('profile.status'))
                            <select name="status" class="w-full border rounded px-3 py-2">
                                <option value="aktif" {{ $student?->status==='aktif'?'selected':'' }}>Aktif</option>
                                <option value="tidak_aktif" {{ $student?->status==='tidak_aktif'?'selected':'' }}>Tidak Aktif</option>
                                <option value="suspended" {{ $student?->status==='suspended'?'selected':'' }}>Ditangguhkan</option>
                            </select>
                        @else
                            <p class="bg-gray-100 px-3 py-2 rounded">{{ $student?->status_label }}</p>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2 flex gap-4 mt-6">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
                    <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                </div>
            </form>
        </div>
        <div class="bg-white rounded px-4 py-6">
            {{-- Section View Dokumen untuk Semua Role --}}
            @include('siswa.partials.documents', ['student' => $user])
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
let cropper;

document.getElementById('photoInput').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    // Validate max size 2MB
    if (file.size > 2 * 1024 * 1024) {
        alert("Ukuran file maksimal 2MB!");
        return;
    }

    const reader = new FileReader();
    reader.onload = function (event) {
        const image = document.getElementById('cropperImage');
        image.src = event.target.result;

        openCropperModal();

        if (cropper) cropper.destroy();
        cropper = new Cropper(image, {
            aspectRatio: 3 / 4,
            viewMode: 1,
            autoCropArea: 1,
            dragMode: "move",
        });
    };

    reader.readAsDataURL(file);
});

function openCropperModal() {
    document.getElementById('cropperModal').classList.remove('hidden');
}
function closeCropperModal() {
    document.getElementById('cropperModal').classList.add('hidden');
    cropper?.destroy();
}

const profileId = document.getElementById('siswa_profile_id').value;

// Crop and Convert to WebP then preview & save to hidden input
document.getElementById('cropButton').addEventListener('click', function () {
    cropper.getCroppedCanvas({
        width: 600,
        height: 800
    }).toBlob(function (blob) {

        let reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function () {
            let base64data = reader.result;

            fetch(`/siswa-profile/${profileId}/update-photo`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({ foto_base64: base64data })
            })
            .then(res => res.json())
            .then(data => {
                console.log("response:", data);
                document.getElementById("photoPreview").src = data.filepath;  // tampilkan preview fix
                closeCropperModal();
            })
            .catch(err => console.error("Error:", err));
        };

    }, "image/webp", 0.9);
});

function openPreviewModal() {
    const modal = document.getElementById('previewModal');
    const preview = document.getElementById('previewImage');
    preview.src = document.getElementById('photoPreview').src;
    modal.classList.remove('hidden');
}

document.getElementById('previewModal').addEventListener('click', function () {
    this.classList.add('hidden');
});

console.log("preview element:", document.getElementById('photoPreview'));
// Remove / Reset Foto
const HolderImg = "https://dummyimage.com/300x400/ccc/000&text=Foto+Siswa";
function removePhoto() {
    const placeholder = HolderImg;
    document.getElementById('photoPreview').src = placeholder;
    document.getElementById('fotoCropped').value = "";
    document.getElementById('photoInput').value = "";
}
</script>
@endpush

@endsection
