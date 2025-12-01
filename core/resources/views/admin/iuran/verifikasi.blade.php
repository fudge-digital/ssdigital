@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran Iuran')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h2 class="text-xl font-bold mb-4">Verifikasi Pembayaran</h2>

    <table class="w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border">Nama Siswa</th>
                <th class="p-2 border">Bulan</th>
                <th class="p-2 border">Jumlah</th>
                <th class="p-2 border">Bukti</th>
                <th class="p-2 border">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($pending as $item)
            <tr>
                <td class="p-2 border">{{ $item->student->name }}</td>
                <td class="p-2 border">{{ $item->bulan }}</td>
                <td class="p-2 border">Rp{{ number_format($item->jumlah) }}</td>
                <td class="p-2 border text-center">
                    @if($item->bukti)
                        <a href="{{ asset('uploads/iuran/'.$item->bukti) }}" target="_blank"
                           class="px-3 py-1 text-xs bg-blue-600 text-white rounded">Lihat Bukti</a>
                    @else
                        -
                    @endif
                </td>
                <td class="p-2 border">
                    <form method="POST" action="{{ route('admin.iuran.approve', $item->id) }}">
                        @csrf
                        <button class="px-4 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                            Approve
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center p-3">Tidak ada pembayaran menunggu verifikasi.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $pending->links() }}
    </div>
</div>
@endsection
