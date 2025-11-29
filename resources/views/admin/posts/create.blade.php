@extends('layouts.app')

@section('title', 'Buat Post Baru')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Buat Post Baru</h1>
        <a href="{{ route('posts.index') }}"
           class="px-4 py-2 rounded-lg border hover:bg-gray-100 transition">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="postForm" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-3 gap-6">
        @csrf

        {{-- LEFT : CONTENT --}}
        <div class="col-span-2 space-y-4">
            {{-- TITLE --}}
            <div>
                <label class="block text-sm font-semibold mb-1 text-gray-700">Judul Post</label>
                <input type="text" name="title"
                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan judul..." value="{{ old('title') }}" required>
                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- EDITOR --}}
            <div>
                <label class="block text-sm font-semibold mb-1 text-gray-700">Konten</label>
                <input type="hidden" name="content" id="content-input">
                <div id="editor" class="min-h-[280px] bg-white border border-gray-300 rounded-lg"></div>
                @error('content') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- RIGHT : IMAGE + CATEGORY --}}
        <div class="space-y-4">
            {{-- IMAGE --}}
            <div class="bg-white shadow rounded-lg p-4">
                <label class="block text-sm font-semibold mb-2 text-gray-700">Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnailInput"
                    class="w-full text-sm border-gray-300 rounded-lg" accept="image/*">

                <img id="previewImage"
                     class="mt-3 w-full h-48 object-contain rounded-lg hidden"
                     alt="Preview Image">

                @error('thumbnail') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- CATEGORY SELECT --}}
            <div class="bg-white shadow rounded-lg p-4">
                <label class="block text-sm font-semibold mb-2 text-gray-700">Kategori</label>
                <select name="category_id"
                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id')==$category->id?'selected':'' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                Publish
            </button>
        </div>

    </form>
</div>
@endsection

@section('scripts')
{{-- CKEditor 5 --}}
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    let editorInstance;

    ClassicEditor
        .create(document.querySelector('#editor'), {
            placeholder: "Tulis konten berita di sini...",
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'underline', '|',
                'link', 'bulletedList', 'numberedList', '|',
                'undo', 'redo'
            ]
        })
        .then(editor => {
            editorInstance = editor;
        })
        .catch(error => {
            console.error(error);
        });

    // Submit handler
    document.getElementById('postForm').addEventListener('submit', function(e) {
        const content = editorInstance.getData().trim();

        if (!content || content === '<p><br></p>') {
            e.preventDefault();
            alert('Konten tidak boleh kosong');
            return false;
        }

        document.getElementById('content-input').value = content;

        console.log("CONTENT:", content);
    });

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
@endsection
