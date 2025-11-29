@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Edit Kategori</h1>

    <form action="{{ route('categories.update', $category->id) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="font-semibold">Nama Kategori</label>
            <input type="text" name="name" value="{{ $category->name }}" required
                   class="w-full mt-1 p-3 border rounded-lg">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <button class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded-lg font-semibold">
            Update
        </button>
    </form>
</div>
@endsection
