@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="postManager">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Berita & Informasi</h2>
        <a href="{{ route('posts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            + Tambah Post
        </a>
    </div>

    {{-- Search + Filter --}}
    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Cari judul..."
               class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-blue-500">

        <select name="category"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ $category == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>

        <button class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900">
            Filter
        </button>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-4 py-3">Judul</th>
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
            </thead>

            <tbody>
            @forelse ($posts as $post)
                <tr class="border-b">
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $post->title }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $post->category->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $post->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('posts.show', $post->slug) }}"
                            class="text-gray-700 py-1 px-2 rounded mr-3 hover:bg-gray-500 hover:text-white transition">View</a>

                        <a href="{{ route('posts.edit', $post->id) }}" 
                            class="text-blue-500 py-1 px-2 rounded hover:bg-blue-500 hover:text-white mr-3 transition">Edit</a>

                        
                        <button type="submit" @click="openDeleteModal = true; setDelete({{ $post->id }})" class="text-red-600 py-1 px-2 rounded hover:bg-red-500 hover:text-white transition">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada data</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>

    <!-- Modal Backdrop -->
    <div x-show="openDeleteModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        x-transition.opacity>

        <!-- Modal Box -->
        <div x-show="openDeleteModal"
            x-transition
            class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">

            <!-- Header -->
            <h2 class="text-xl font-semibold text-gray-800 mb-2">
                Hapus Post?
            </h2>

            <!-- Description -->
            <p class="text-gray-600 text-sm mb-6 leading-relaxed">
                Apakah Anda yakin ingin menghapus post ini? Tindakan ini tidak dapat dibatalkan.
            </p>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
                <!-- Button Batal -->
                <button type="button"
                    @click="openDeleteModal = false"
                    class="button-base border border-blue-600 text-blue-600 hover:bg-blue-50">
                    Batal
                </button>

                <!-- Button Hapus -->
                <form id="deleteForm" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="button-base bg-red-600 text-white hover:bg-red-700">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

<style>
    .button-base {
        @apply px-5 py-2.5 text-sm font-medium rounded-lg transition-all duration-200;
    }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('postManager', () => ({
        openDeleteModal: false,
        deletePostId: null,
        setDelete(id) {
            this.deletePostId = id;
            document.getElementById('deleteForm').action = '/posts/' + id;
        }
    }))
})
</script>