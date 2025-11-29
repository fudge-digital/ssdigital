@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6"
    x-data="{ 
        openModalUpload:false, 
        openModalBukti:false, 
        openModalRequest3:false, 
        openModalRequest6:false,
        buktiSrc:'', 
        catatan:'' 
    }">

    {{-- HEADER --}}
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Iuran Bulanan</h2>
    <p class="text-sm mb-8 text-gray-600">(*) Jika total untuk lebih dari 1 anak, cukup upload bukti sekali saja.</p>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @php
        $children = Auth::user()->children;
        $childNames = $children->pluck('name');
        $total = $iuran->sum('jumlah');
        $latestMonth = $iuran->first()->bulan ?? null;
    @endphp

    {{-- GRAND TOTAL CARD --}}
    <div class="bg-white rounded-xl shadow-md border p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold">Total Iuran Bulan Ini</h3>
            <p class="text-2xl font-extrabold text-blue-700">
                Rp {{ number_format($total,0,',','.') }}
            </p>
            <p class="text-sm text-gray-600">
                <strong>Untuk siswa:</strong>
                <ul>
                    @foreach($childNames as $child)
                        <li class="text-sm">- {{ $child }}</li>
                    @endforeach
                </ul>
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            {{-- REQUEST 3 BULAN --}}
            <button @click="openModalRequest3 = true"
                    class="px-5 py-2 bg-orange-600 text-white text-sm rounded-xl hover:bg-orange-700 shadow">
                Request Tagihan 3 Bulan
            </button>

            {{-- REQUEST 6 BULAN --}}
            <button @click="openModalRequest6 = true"
                    class="px-5 py-2 bg-purple-600 text-white text-sm rounded-xl hover:bg-purple-700 shadow">
                Request Tagihan 6 Bulan
            </button>

            {{-- UPLOAD BUTTON --}}
            @if($iuran->where('status','unpaid')->count())
                <button @click="openModalUpload = true"
                        class="px-5 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 shadow">
                    Upload Bukti Pembayaran
                </button>
            @endif

            {{-- WHATSAPP --}}
            <a href="https://wa.me/62895606432020?text=Halo admin, saya parent dari {{ $childNames }}. Saya ingin konfirmasi pembayaran iuran bulan {{ \Carbon\Carbon::createFromFormat('Y-m',$latestMonth)->translatedFormat('F Y') }} dengan total Rp {{ number_format($total,0,',','.') }}. Mohon untuk diverifikasi. Terima kasih."
                target="_blank"
                class="px-5 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700 shadow">
                Kirim WA
            </a>
        </div>
    </div>

    {{-- CARD LIST PER ANAK --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($iuran as $item)
            <div class="bg-white border rounded-xl shadow p-4">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-bold text-gray-800">{{ $item->siswa->name }}</h4>

                    {{-- STATUS --}}
                    @if ($item->status === 'paid')
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                            Sudah Terbayar
                        </span>
                    @elseif ($item->bukti)
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                            Pending Verifikasi
                        </span>
                    @else
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                            Belum Terbayar
                        </span>
                    @endif
                </div>

                <p class="text-sm text-gray-600">
                    Bulan: <b>{{ \Carbon\Carbon::createFromFormat('Y-m', $item->bulan)->translatedFormat('F Y') }}</b>
                </p>

                <p class="text-sm text-gray-600">
                    Jumlah: <b>Rp {{ number_format($item->jumlah,0,',','.') }}</b>
                </p>

                @if($item->status === 'paid' && $item->bukti)
                    <button @click="openModalBukti = true; buktiSrc='{{ Storage::url($item->bukti) }}'; catatan='{{ $item->catatan }}'"
                        class="mt-4 w-full bg-gray-800 text-white p-3 rounded-lg text-sm hover:bg-black">
                        Lihat Bukti Pembayaran
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-5">
        {{ $iuran->links() }}
    </div>


    {{-- ================= MODAL REQUEST 3 BULAN ================= --}}
    <div x-show="openModalRequest3" x-cloak
        class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50">

        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">
            <h3 class="text-lg font-bold mb-4">Request Tagihan 3 Bulan ke Depan</h3>
            <p class="text-sm text-gray-600 mb-4">Tagihan akan dibuat otomatis untuk 3 bulan ke depan dari bulan terakhir iuran yang ada.</p>

            <form action="{{ route('iuran.request') }}" method="POST">
                @csrf
                <input type="hidden" name="months" value="3">

                <div class="flex justify-end gap-2">
                    <button type="button" @click="openModalRequest3=false"
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        Ya, Buat Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL REQUEST 6 BULAN ================= --}}
    <div x-show="openModalRequest6" x-cloak
        class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50">

        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">
            <h3 class="text-lg font-bold mb-4">Request Tagihan 6 Bulan ke Depan</h3>
            <p class="text-sm text-gray-600 mb-4">Tagihan akan dibuat untuk 6 bulan berikutnya.</p>

            <form action="{{ route('iuran.request') }}" method="POST">
                @csrf
                <input type="hidden" name="months" value="6">

                <div class="flex justify-end gap-2">
                    <button type="button" @click="openModalRequest6=false"
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Ya, Buat Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL LIHAT BUKTI ================= --}}
    <div x-show="openModalBukti" x-cloak
        class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50">

        <div class="bg-white rounded-2xl w-full max-w-lg p-4 shadow-xl">
            <img :src="buktiSrc" class="w-full rounded-lg mb-4 object-contain max-h-[70vh]">
            <p class="text-sm font-bold">Catatan Pembayaran:</p>
            <p class="text-sm mb-4" x-text="catatan ? catatan : '-'"></p>
            <button @click="openModalBukti=false"
                class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Tutup
            </button>
        </div>
    </div>

</div>
@endsection
