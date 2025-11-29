@extends('layouts.app')

@section('title', 'Berita & Informasi')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- PAGE TITLE --}}
    <h2 class="text-3xl font-bold mb-6 text-gray-800">Berita & Informasi</h2>

    {{-- SEARCH BAR --}}
    <form method="GET" action="{{ route('posts.public') }}" class="mb-6">
        <div class="flex items-center bg-white border rounded-full px-4 py-2 shadow-sm">
            <input 
                type="text"
                name="search"
                placeholder="Cari berita..."
                value="{{ request('search') }}"
                class="flex-1 outline-none text-sm bg-transparent"
            />
            <button type="submit" class="text-gray-600 hover:text-gray-900">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    {{-- CATEGORY FILTER --}}
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('posts.public', array_filter(['search' => request('search')])) }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium border
           {{ !request('category') ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100' }}">
            Semua
        </a>

        @foreach ($categories as $cat)
            <a href="{{ route('posts.public', array_filter(['category' => $cat->slug, 'search' => request('search')])) }}"
               class="px-3 py-1.5 rounded-full text-sm font-medium border
               {{ request('category') === $cat->slug ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 hover:bg-gray-100 border-gray-300' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    {{-- POSTS GRID --}}
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($posts as $post)
                <a href="{{ route('posts.show', $post->slug) }}"
                   class="block bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-200 overflow-hidden">

                    @if($post->thumbnail)
                        <img src="{{ asset('storage/' . $post->thumbnail) }}" class="h-40 w-full object-cover">
                    @else
                        <div class="h-40 w-full bg-gray-200 flex items-center justify-center text-gray-500">
                            Tidak ada gambar
                        </div>
                    @endif

                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">
                            {{ $post->title }}
                        </h3>
                        <p class="text-sm text-gray-500 line-clamp-3 mb-3">{{ $post->excerpt }}</p>

                        <div class="flex justify-between text-xs text-gray-400">
                            <span>{{ $post->created_at->format('d M Y') }}</span>
                            <span>{{ $post->author->name ?? 'Admin' }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        <div class="mt-10">
            {{ $posts->appends(request()->query())->links('pagination::tailwind') }}
        </div>

    @else
        <p class="text-gray-500 text-center py-12 text-lg">Tidak ada berita ditemukan.</p>
    @endif

</div>
@endsection
