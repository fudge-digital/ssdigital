@extends('layouts.app')

@section('title', 'Dashboard Orang Tua')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{ openModal: false }">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">
        Halo, {{ $parent->name }} 👋
    </h2>

    {{-- CARD LIST SISWA --}}
    <h4 class="col-span-full text-lg font-semibold text-gray-700 mb-4">Daftar Siswa Anda</h4>
    <div class="grid md:grid-cols-3 lg:grid-cols-3 gap-6 mb-10">
        @forelse($students as $student)
            <div class="bg-white shadow-md rounded-2xl p-5 border">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-lg">{{ $student->name }}</h3>
                    <span class="px-3 py-1 text-sm rounded-full 
                        {{ $student->siswaProfile->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($student->siswaProfile->status_label) }}
                    </span>
                </div>
                <p class="text-gray-600 text-sm mb-1">NISS: {{ $student->profile->niss ?? '-' }}</p>
                <p class="text-gray-600 text-sm mb-1">Email: {{ $student->email }}</p>
                <p class="text-gray-600 text-sm mb-1">Usia: {{ $student->profile->usia }} tahun</p>
                <p class="text-gray-600 text-sm">Kategori Umur: {{ $student->profile->kelompok_umur }} {{ $student->profile->jenis_kelamin_label }}</p>
                <div class="mt-4">
                    @if($student->siswaProfile->status !== 'tidak_aktif')
                        <a href="{{ route('siswa.show', $student->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded text-xs">
                            Detail Siswa
                        </a>
                    @else
                        <a href="#" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-3 rounded text-xs ml-2 cursor-not-allowed opacity-50">
                            Detail Siswa
                        </a>
                    @endif
                    
                    @if($student->siswaProfile->status !== 'tidak_aktif')
                        <a href="{{ route('siswa.edit', $student->id) }}" class="bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded text-xs ml-2">
                            Edit Data Siswa
                        </a>
                    @else
                        <a href="#" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-3 rounded text-xs ml-2 cursor-not-allowed opacity-50">
                            Edit Data Siswa
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <p>Tidak ada data siswa yang terdaftar.</p>
        @endforelse
    </div>

    {{-- Documents & Jadwal Latihan--}}
    @if($student->siswaProfile->status === 'aktif')
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h2 class="col-span-full text-lg font-semibold text-gray-700 mb-4">Jadwal Latihan Bulan {{ now()->format('F Y') }}</h2>
                <div class="bg-white shadow-lg rounded-xl px-4 py-4 pb-4">
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
            @include('partials.iuran-summary')
        </div>
    @endif

    {{-- INFO PEMBAYARAN --}}
    @if ($nonActiveStudents->count() > 0) 
        <h3 class="text-lg font-semibold mt-6">🔔 Informasi Pendaftaran</h3>
        <div class="grid md:grid-cols-3 lg:grid-cols-3 gap-6 mb-10">
            <div class="text-sm p-6 text-gray-900">
                <ol class="list-decimal list-inside space-y-2">
                    <li>
                        Biaya pendaftaran sebesar <strong>Rp 650.000,-</strong> untuk setiap siswa.
                        <ul class="list-disc list-inside ml-6 mt-1">
                            <li>Sudah termasuk iuran 1 bulan pertama</li>
                            <li>
                                Mendapatkan 1 set jersey latihan dengan jersey atasan reverse/bolak-balik
                                <a href="#" class="text-blue-500 underline">(lihat jersey latihan)</a>
                            </li>
                            <li>Mendapatkan Nomor Induk Satria Siliwangi (NISS) yang berlaku selamanya</li>
                            <li>Iuran perbulan adalah sebesar<strong>Rp 325.000,-</strong></li>
                        </ul>
                    </li>
                    <li>Silakan konfirmasi pembayaran dan unggah bukti pembayaran melalui formulir di samping.</li>
                    <li>Setelah pembayaran pendaftaran diverifikasi oleh admin, status siswa akan aktif dan mendapatkan NISS.</li>
                </ol>
            </div>
            
            @if(!$pembayaran)
            <div class="bg-gray-100 border border-gray-200 text-sm rounded-2xl p-6 text-gray-900">
                
                Silakan lakukan pembayaran sejumlah yang tertera dibawah ke 
                <p class="py-2">
                <strong>BCA a/n Satria Siliwangi<br>
                No. Rek. 7773075176</strong>
                </p>
                dan unggah bukti pembayaran pendaftaran untuk melanjutkan proses aktivasi akun siswa.</p>

                @if($errors->any())
                    <div class="p-3 bg-red-200 text-red-800 rounded mb-2">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="p-3 bg-green-200 text-green-800 rounded mb-2">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('parent.upload-pembayaran', $student->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <div class="relative mb-3">
                        <input type="hidden" name="siswa_ids" value="{{ $nonActiveStudents->pluck('id')->join(',') }}">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-bold">Rp</span>
                        <input 
                            type="text" 
                            name="jumlah" 
                            id="jumlah_{{ $student->id }}"
                            value="{{ number_format($totalPendaftaran, 0, ',', '.') }}" readonly
                            class="border border-gray-300 bg-gray-300 rounded-lg p-2 w-full text-sm text-gray-500 font-bold pl-10 pr-3 jumlah-input"
                            inputmode="numeric" 
                            autocomplete="off">
                    </div>
                    <label>Upload Bukti Pembayaran</label>
                    <input type="file" name="bukti_pembayaran" accept="image/*" required
                        class="border border-gray-300 bg-white rounded-lg p-2 w-full mb-4">
                    <button type="submit"
                            class="bg-green-700 hover:bg-green-800 text-white font-semibold py-2 px-6 rounded-lg">
                        Verifikasi Pembayaran
                    </button>
                </form>
            </div>
            @else($pembayaran && $pembayaran->status === 'pending')
                @php
                    $message = "Halo, Saya ingin konfirmasi pembayaran pendaftaran siswa Satria Siliwangi Basketball atas nama *$parent->name*";
                    $message .= "\n\nDengan detail sebagai berikut:";
                    $message .= "\n• Jumlah Siswa: " . $nonActiveStudents->count();
                    $message .= "\n• Nama Siswa: " . implode(', ', $nonActiveStudents->pluck('name')->toArray());
                    $message .= "\n• Jumlah Pembayaran: Rp " . number_format($totalPendaftaran, 0, ',', '.');
                    $message .= "\n• Tanggal Pembayaran: " . $pembayaran->created_at->format('d-m-Y H:i');
                    $message .= "\n\nMohon verifikasi pembayaran saya. Terima kasih.";
                @endphp
                <div>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                        <p class="font-bold mb-2">Menunggu Verifikasi</p>
                        <p>Bukti pembayaran Anda telah diterima dan sedang menunggu verifikasi dari admin. Mohon tunggu proses ini.</p>
                        <p>
                            Jika menurut anda ini terlalu lama, silahkan hubungi admin melalui<br>
                            <a href="https://wa.me/62895606432020?text={{ urlencode($message) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-1 mt-5 mb-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-full shadow">
                                <i class="fa-brands fa-whatsapp text-lg"></i>
                                WhatsApp Admin
                            </a>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.jumlah-input').forEach(function(input) {
            input.addEventListener('input', function(e) {
                // Hapus karakter selain angka
                let value = e.target.value.replace(/[^\d]/g, '');

                // Format angka dengan titik ribuan
                if (value) {
                    e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                } else {
                    e.target.value = '';
                }
            });

            // Saat submit form, ubah ke angka murni tanpa titik
            input.closest('form').addEventListener('submit', function() {
                input.value = input.value.replace(/\./g, '');
            });
        });
    });
    </script>
    @endpush