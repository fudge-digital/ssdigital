@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Kategori Posting</h1>
        <a href="{{ route('categories.create') }}"
           class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg">
            + Tambah Kategori
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Slug</th>
                    <th class="p-3 w-32 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($categories as $cat)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">{{ $cat->name }}</td>
                    <td class="p-3 text-gray-600">{{ $cat->slug }}</td>
                    <td class="p-3 flex justify-center gap-2">
                        <a href="{{ route('categories.edit', $cat->id) }}" class="text-blue-600 hover:underline">Edit</a>

                        <form action="{{ route('categories.destroy', $cat->id) }}" method="POST"
                              onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
</div>
@endsection
