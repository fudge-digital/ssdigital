@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Post</h2>

    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        <!-- LEFT Side : Title & Content -->
        <div class="md:col-span-2 space-y-4">

            <div>
                <label class="font-semibold text-gray-700">Judul</label>
                <input type="text" name="title" value="{{ old('title', $post->title) }}"
                       class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="font-semibold text-gray-700">Konten</label>
                <textarea name="content" id="editor" rows="12" class="hidden">{{ old('content', $post->content) }}</textarea>
            </div>

        </div>

        <!-- RIGHT Side : Category, Thumbnail, Submit -->
        <div class="space-y-4">

            <div>
                <label class="font-semibold text-gray-700">Kategori</label>
                <select name="category_id"
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $post->category_id == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label>Tanggal Posting / Jadwal</label>
                <input type="date" name="published_at" class="form-control"
                    value="{{ old('published_at', $post->published_at->format('Y-m-d')) }}">
            </div>

            <div>
                <label class="font-semibold text-gray-700">Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnailInput"
                       class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">

                @if ($post->thumbnail)
                    <p class="text-sm mt-2 text-gray-600">Thumbnail saat ini:</p>
                    <img id="previewImage" src="{{ asset('storage/' . $post->thumbnail) ?? 'No Thumbnail' }}"
                         class="w-full h-40 object-cover rounded-lg shadow">
                @endif
            </div>

            <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                Update Post
            </button>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#editor'))
</script>

<script>
    // Preview thumbnail
    document.getElementById('thumbnailInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('previewImage');
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        }
    });
</script>
@endpush
