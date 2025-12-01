@extends('layouts.app')

@section('title', $post->title)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">

        {{-- Navigation Back --}}
        <a href="{{ backPost() }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-6">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Berita
        </a>

        {{-- Thumbnail Hero Section --}}
        <div class="w-full rounded-2xl overflow-hidden shadow-md mb-6">
            @if($post->thumbnail)
                <img src="{{ asset('storage/' . $post->thumbnail) }}" class="w-full h-72 object-cover">
            @else
                <div class="w-full h-72 bg-gray-300 flex items-center justify-center">
                    <span class="text-gray-600">Tidak ada gambar</span>
                </div>
            @endif
        </div>

        {{-- Post Header --}}
        <div class="max-w-4xl mx-auto">
            <div class="flex flex-wrap gap-2 mb-3">
                <span class="px-3 py-1 bg-green-600 text-white text-xs rounded-full">
                    {{ $post->category->name ?? 'Tanpa Kategori' }}
                </span>
            </div>

            <h1 class="text-4xl font-bold text-gray-800 leading-tight mb-4">
                {{ $post->title }}
            </h1>

            {{-- Meta --}}
            <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
                <span><i class="fa-solid fa-user mr-1"></i> {{ $post->author->name ?? 'Admin' }}</span>
                <span><i class="fa-solid fa-calendar mr-1"></i> {{ $post->created_at->format('d M Y') }}</span>
            </div>

            {{-- Content --}}
            <article class="prose prose-lg max-w-none prose-img:rounded-xl prose-headings:text-gray-800 prose-p:text-gray-700">
                {!! $post->content !!}
            </article>

            {{-- Share Buttons --}}
            <div class="mt-10 border-t pt-6">
                <h3 class="text-lg font-semibold mb-3">Bagikan</h3>

                <div class="flex gap-3">
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . request()->fullUrl()) }}"
                       target="_blank"
                       class="bg-green-500 px-4 py-2 rounded-lg hover:text-white">
                        <i class="fa-brands fa-whatsapp mr-1"></i> WhatsApp
                    </a>
                    <button onclick="copyToClipboard('{{ request()->fullUrl() }}')"
                            class="bg-gray-300 px-4 py-2 rounded-lg hover:text-white hover:bg-gray-600">
                        <i class="fa-solid fa-clipboard mr-1"></i> Salin Link
                    </button>
                </div>
            </div>

            {{-- Recommended Posts --}}
            @if($relatedPosts->count())
                <div class="mt-14 border-t pt-10">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Berita Lainnya</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($relatedPosts as $item)
                            <a href="{{ route('posts.show', $item->slug) }}"
                               class="block bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-200 p-4 transition">

                                <h4 class="font-semibold text-gray-800 line-clamp-2 mb-1">
                                    {{ $item->title }}
                                </h4>
                                <p class="text-sm text-gray-500 line-clamp-2">{{ $item->excerpt }}</p>
                                <span class="text-xs text-gray-400">{{ $item->created_at->format('d M Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Copy to clipboard script --}}
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert("Link berhasil disalin!");
    }
</script>
@endsection
