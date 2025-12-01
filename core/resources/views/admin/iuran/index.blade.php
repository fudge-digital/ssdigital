@extends('layouts.app')

@section('title', 'Manajemen Iuran Bulanan')

@section('content')
<div class="container mx-auto px-4 py-6">

    <form id="bulkForm" action="{{ route('admin.iuran.bulkVerify') }}" method="POST">
        @csrf

        <div x-data="{ modalOpen:false, modalType:'preview', buktiUrl:'', catatan:'', actionUrl:'' }">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Manajemen Iuran Bulanan</h2>

                <div class="flex gap-2">
                    {{-- GENERATE BULAN INI --}}
                    <form action="{{ route('admin.iuran.generate') }}" method="POST"
                          onsubmit="return confirm('Yakin generate tagihan bulan ini? Jika sudah ada akan ditolak.')">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition shadow">
                            Generate Tagihan Bulan Ini
                        </button>
                    </form>

                    {{-- GENERATE BULK --}}
                    <button type="button"
                        @click="modalType='generate'; modalOpen=true;"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition shadow">
                        Generate Tagihan Bulk
                    </button>
                </div>
            </div>

            <div class="flex justify-start mb-4">
                <button type="button" id="btnBulkVerify"
                        class="px-2 py-1 bg-green-600 text-sm text-white rounded disabled:opacity-50"
                        @click="modalType='bulk'; modalOpen=true; actionUrl='{{ route('admin.iuran.bulkVerify') }}'"
                        disabled>
                    Verifikasi Pilihan
                </button>
            </div>

            {{-- TABLE --}}
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-100 text-sm text-left">
                        <th class="p-2 border text-center">
                            <input type="checkbox" id="selectAll"
                                   @change="document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = $el.checked)">
                        </th>
                        <th class="p-2 border">Nama Orang Tua</th>
                        <th class="p-2 border">Nama Siswa</th>
                        <th class="p-2 border">Bulan</th>
                        <th class="p-2 border">Jumlah</th>
                        <th class="p-2 border">Status</th>
                        <th class="p-2 border">Tanggal Bayar</th>
                        <th class="p-2 border">Bukti Pembayaran</th>
                        <th class="p-2 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($iuran as $item)
                    <tr class="text-sm">
                        <td class="p-2 border text-center">
                            <input type="checkbox" name="iuran_ids[]" value="{{ $item->id }}" class="rowCheck w-4 h-4">
                        </td>
                        <td class="p-2 border">{{ $item->siswa->parents->first()->userProfile->nama_ayah ?? '-' }}</td>
                        <td class="p-2 border">{{ $item->siswa->name }}</td>
                        <td class="p-2 border">{{ $item->bulan ? \Carbon\Carbon::parse($item->bulan)->format('F Y') : '-' }}</td>
                        <td class="p-2 border">Rp{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td class="p-2 border">
                            @if($item->status == 'paid')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">LUNAS</span>
                            @elseif($item->status == 'pending')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">MENUNGGU</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">BELUM TERBAYAR</span>
                            @endif
                        </td>
                        <td class="p-2 border">{{ $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->format('d-m-Y') : '-' }}</td>

                        <td class="p-2 border space-x-1">
                            @if($item->bukti)
                                <button type="button"
                                        @click="modalType='preview'; modalOpen=true; buktiUrl='{{ Storage::url($item->bukti) }}'; catatan='{{ $item->catatan }}'"
                                        class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Lihat Bukti
                                </button>
                            @endif
                        </td>

                        @if($item->status == 'paid')
                        <td class="p-2 border">
                            <span class="px-3 py-1 text-xs text-green-600 rounded">Sudah Terverifikasi</span>
                        </td>
                        @else
                        <td class="p-2 border">
                            <button type="button"
                                    @click="modalType='verify'; modalOpen=true; actionUrl='{{ route('admin.iuran.approve', $item->id) }}'"
                                    class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                Verifikasi
                            </button>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="9" class="p-3 text-center">Belum ada data</td></tr>
                @endforelse
                </tbody>
            </table>

            {{-- MODAL --}}
            <div x-show="modalOpen" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/60 z-50">
                <div class="bg-white rounded-lg shadow-xl p-5 max-w-lg w-full" @click.outside="modalOpen=false">

                    {{-- PREVIEW --}}
                    <template x-if="modalType === 'preview'">
                        <div>
                            <h3 class="text-lg font-bold mb-2">Bukti Pembayaran</h3>
                            <img :src="buktiUrl" class="w-full rounded-lg mb-4 object-contain max-h-[70vh]">
                            <p class="font-bold text-sm">Catatan Pembayaran:</p>
                            <p class="text-black text-sm mb-4" x-text="catatan || '-'"></p>
                            <button type="button" @click="modalOpen=false"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Tutup</button>
                        </div>
                    </template>

                    {{-- VERIFY SINGLE --}}
                    <template x-if="modalType === 'verify'">
                        <div>
                            <h2 class="font-semibold text-lg mb-3">Konfirmasi Verifikasi</h2>
                            <p class="text-sm mb-4">Apakah Anda yakin ingin menyetujui pembayaran ini?</p>

                            <form :action="actionUrl" method="POST" class="flex justify-end space-x-2">
                                @csrf
                                <button type="button" @click="modalOpen=false"
                                        class="px-4 py-1.5 bg-gray-300 rounded hover:bg-gray-400 text-sm">Batal</button>
                                <button type="submit"
                                        class="px-4 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Approve</button>
                            </form>
                        </div>
                    </template>

                    {{-- BULK VERIFY --}}
                    <template x-if="modalType === 'bulk'">
                        <div>
                            <h2 class="text-lg font-bold mb-4">Konfirmasi Bulk Verifikasi</h2>
                            <p class="text-sm mb-4">
                                Anda akan memverifikasi beberapa pembayaran sekaligus. Lanjutkan?
                            </p>

                            <form id="bulkForm" method="POST" :action="actionUrl" class="flex justify-end space-x-2">
                                @csrf
                                <button type="button" @click="modalOpen=false"
                                        class="px-4 py-1.5 bg-gray-300 rounded hover:bg-gray-400 text-sm">Batal</button>
                                <button type="submit"
                                        class="px-4 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Approve Semua</button>
                            </form>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </form>

    <div class="mt-4">
        {{ $iuran->links() }}
    </div>

</div>
@endsection

<script>
document.addEventListener("DOMContentLoaded", () => {
    const checkboxes = document.querySelectorAll(".rowCheck");
    const selectAll = document.getElementById("selectAll");
    const btnBulk = document.getElementById("btnBulkVerify");

    function updateBulkButton() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        btnBulk.disabled = !anyChecked;
    }

    checkboxes.forEach(cb => cb.addEventListener("change", updateBulkButton));
    selectAll.addEventListener("change", () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkButton();
    });
});
</script>
