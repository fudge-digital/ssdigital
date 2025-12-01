@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran Pendaftaran')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Verifikasi Pembayaran Pendaftaran</h2>

    {{-- Alert sukses --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 border border-green-300 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- Alert error --}}
    @if($errors->any())
        <div class="bg-red-100 text-red-800 border border-red-300 px-4 py-3 rounded-lg mb-6">
            {{ implode(', ', $errors->all()) }}
        </div>
    @endif

    <div class="bg-white shadow rounded-2xl overflow-hidden border border-gray-200">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-50 border-b text-gray-900 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Orang Tua</th>
                    <th class="px-4 py-3 text-left">Anak Terdaftar</th>
                    <th class="px-4 py-3 text-left">Jumlah Pembayaran</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Bukti Pembayaran</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $p)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $p->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $p->user->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($p->details->count() > 0)
                                <ul class="list-disc list-inside text-xs text-gray-600">
                                    @foreach($p->details as $detail)
                                        <li>{{ $detail->siswa->name ?? '-' }}</li>
                                    @endforeach
                                </ul>
                            @elseif($p->siswa)
                                {{ $p->siswa->name }}
                            @else
                                <span class="text-gray-400 italic">Tidak ada data</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold">Rp {{ number_format($p->jumlah_total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $p->status_badge_class }}">
                                {{ $p->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $fileUrl = $p->bukti_pembayaran
                                    ? asset('storage/' . $p->bukti_pembayaran)
                                    : null;
                            @endphp

                            @if($fileUrl)
                            <button
                                data-modal-target="previewModal"
                                data-modal-toggle="previewModal"
                                onclick="setPreviewContent(@js($fileUrl))"
                                class="bg-blue-500 text-white hover:bg-blue-600 text-xs px-3 py-1 rounded-xl">
                                Lihat Bukti Pembayaran
                            </button>
                            @else
                                <span class="bg-gray-500 text-white text-xs px-3 py-1 rounded-xl">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center space-x-2">
                            @if($p->status !== 'verified')
                                <form action="{{ route('admin.pembayaran.verify', $p->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded-lg">
                                        Verifikasi
                                    </button>
                                </form>
                            @endif

                            @if($p->status === 'verified')
                                <form action="{{ route('admin.pembayaran.suspend', $p->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs px-3 py-1 rounded-lg">
                                        Suspensi
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 py-6">Belum ada data pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div id="previewModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 bg-black bg-opacity-60 flex justify-center items-center">
            <div class="relative p-4 w-full max-w-3xl max-h-[85vh]">
                <div class="relative bg-white rounded-lg shadow-lg overflow-hidden">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold">Preview Bukti Pembayaran</h3>
                        <button type="button" data-modal-hide="previewModal"
                                class="text-gray-500 hover:text-gray-700 text-2xl font-bold">✕</button>
                    </div>

                    <!-- Content -->
                    <div id="modalContent" class="p-4 overflow-y-auto max-h-[75vh]">
                        <!-- dynamic content -->
                    </div>
                    
                    <div id="modalFooter" class="p-4 border-t text-right">
                        <a href="{{ $fileUrl }}" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function setPreviewContent(url) {
        const content = document.getElementById("modalContent");
        const ext = url.split('.').pop().toLowerCase();

        if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
            content.innerHTML = `<img src="${url}" class="w-full max-h-[70vh] object-contain rounded" />`;
        } else if (ext === 'pdf') {
            content.innerHTML = `<embed src="${url}" type="application/pdf" class="w-full h-[70vh]" />`;
        } else {
            content.innerHTML = `<p class="text-gray-500">Format tidak didukung</p>`;
        }
    }
</script>
@endpush