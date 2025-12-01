@extends('layouts.app')

@section('title', 'Daftar Orang Tua')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-6">Daftar Orang Tua & Promo</h2>

    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border text-left">
        <thead>
            <tr class="bg-gray-100 text-sm">
                <th class="p-2 border">No.</th>
                <th class="p-2 border">Nama Parent</th>
                <th class="p-2 border">Jumlah Siswa</th>
                <th class="p-2 border">Promo Aktif</th>
                <th class="p-2 border">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parents as $parent)
                <tr class="text-sm">
                    <td class="p-2 border">{{ $loop->iteration }}</td>
                    <td class="p-2 border">{{ $parent->name }}</td>
                    <td class="p-2 border">{{ $parent->children_count }}</td>

                    {{-- Badge promo --}}
                    <td class="p-2 border">
                        @if($parent->promo_type === null || $parent->promo_type === 'none')
                            <span class="px-2 py-1 bg-gray-400 text-white rounded text-sm">Tidak Ada</span>
                        @elseif($parent->promo_type === 'sibling')
                            <span class="px-2 py-1 bg-blue-600 text-white rounded text-sm">Sibling</span>
                        @elseif($parent->promo_type === 'sponsor')
                            <span class="px-2 py-1 bg-green-600 text-white rounded text-sm">Sponsor</span>
                        @elseif($parent->promo_type === 'beasiswa')
                            <span class="px-2 py-1 bg-purple-600 text-white rounded text-sm">Beasiswa</span>
                        @endif
                    </td>

                    <td class="p-2 border">
                        {{-- Dropdown hanya muncul jika anak > 1 --}}
                        @if($parent->children_count > 1)
                            <form method="POST" action="{{ route('parents.updatePromo', $parent->id) }}">
                                @csrf

                                <select name="promo_type" class="border rounded px-2 py-1 text-sm">
                                    @foreach(['none','sibling','sponsor','beasiswa'] as $type)
                                        <option class="text-sm capitalize" value="{{ $type }}" {{ $parent->promo_type === $type ? 'selected' : '' }}>
                                            {{ strtoupper($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm ml-2">
                                    Update
                                </button>
                            </form>
                        @else
                            <span class="text-gray-500 text-sm italic">Tidak Eligible</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $parents->links() }}
    </div>
</div>
@endsection
