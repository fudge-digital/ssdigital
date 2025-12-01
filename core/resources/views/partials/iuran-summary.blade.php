<div>
    <h2 class="col-span-full text-lg font-semibold text-gray-700 mb-4">Iuran Bulanan {{ now()->format('F Y') }}</h2>
    <div class="bg-white shadow-lg rounded-xl grid grid-cols-2 gap-4 px-4 py-4 items-center">
        @if($iuranSummary['countPending'] > 0)
            <div>
                <p class="text-xl font-bold text-red-600 mt-2">
                    {{ $iuranSummary['countPending'] }} tagihan belum terbayar
                </p>
                <p class="text-sm text-gray-800">
                    Jumlah Tagihan Sebesar Rp. {{ number_format($iuranSummary['totalPending'], 0, ',', '.')}} 
                </p>
            </div>
        @else
            <div>
                <p class="text-xl font-bold text-green-600 mt-2">Tagihan Terbayarkan</p>
                <p class="text-sm text-gray-800">
                    Jumlah Tagihan Sebesar Rp. 0.-
                </p>
            </div>
        @endif

        <div class="text-right">
            <a href="{{ route('parent.iuran.index') }}"
            class="inline-block mt-3 bg-blue-600 text-white px-2 py-1 rounded text-sm hover:text-white hover:bg-blue-500 transitions">
                Lihat detail
            </a>
        </div>
    </div>
</div>