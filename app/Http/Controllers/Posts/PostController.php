<?php

namespace App\Http\Controllers\Posts;

use App\Models\Post;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');

        $posts = Post::with('category')
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($category, fn($q) => $q->where('category_id', $category))
            ->latest()
            ->paginate(10);

        $categories = Category::all();

        return view('admin.posts.index', compact('posts', 'categories', 'search', 'category'));
    }

    public function public(Request $request)
    {
        $hiddenCategories = ['jadwal-latihan'];
        $query = Post::with('category','author')
        ->whereHas('category', function($q) use ($hiddenCategories){
            $q->whereNotIn('slug', $hiddenCategories);
        })
        ->latest();

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) =>
                $q->where('slug', $request->category)
            );
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                ->orWhere('content', 'like', '%'.$request->search.'%');
            });
        }

        $posts = $query->paginate(9);
        
        $categories = Category::whereNotIn('slug', $hiddenCategories)
        ->orderBy('name')
        ->get();

        return view('posts.public', compact('posts','categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|min:3',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['user_id'] = auth()->id();
        $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);

        // Upload gambar thumbnail jika ada
        if ($request->hasFile('thumbnail')) {

            $file = $request->file('thumbnail');
            $filename = 'post_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Cek env
            $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

            if (!$useDirectPublicStorage && file_exists(public_path('storage'))) {
                // 🖥️ LOCAL: simpan lewat disk 'public' (storage/app/public)
                $path = $file->storeAs('posts', $filename, 'public');
            } else {
                // 🌐 SERVER: simpan langsung ke public_html/storage/posts
                $destinationPath = public_path('storage/posts');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $path = 'posts/' . $filename;
            }

            $data['thumbnail'] = $path;
        }

        Post::create($data);

        return redirect()->route('posts.index')->with('success', 'Post berhasil dibuat');
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        return view('admin.posts.edit', compact('post','categories'));
    }

    public function update(Request $request, Post $post)
    {

        $data = $request->validate([
            'title' => 'required|min:3',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'published_at' => 'nullable|date',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);
        $data['published_at'] ;

        // Jika ada file baru
        if ($request->hasFile('thumbnail')) {

            $file = $request->file('thumbnail');
            $filename = 'post_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Check env untuk multi environment
            $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

            if (!$useDirectPublicStorage && file_exists(public_path('storage'))) {
                // 🖥️ LOCAL
                $path = $file->storeAs('posts', $filename, 'public');

                // Hapus file lama jika ada
                if ($post->thumbnail && file_exists(public_path('storage/' . $post->thumbnail))) {
                    unlink(public_path('storage/' . $post->thumbnail));
                }

            } else {
                // 🌐 SERVER
                $destinationPath = public_path('storage/posts');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $path = 'posts/' . $filename;

                // Hapus file lama jika ada
                if ($post->thumbnail && file_exists(public_path('storage/' . $post->thumbnail))) {
                    unlink(public_path('storage/' . $post->thumbnail));
                }
            }

            $data['thumbnail'] = $path;
        }

        $post->update($data);

        return redirect()->route('posts.index')->with('success', 'Post berhasil diperbarui');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Post berhasil dihapus');
    }

    public function show($slug)
    {
        $post = Post::with('author','category')->where('slug', $slug)->firstOrFail();

        $relatedPosts = Post::where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest()
            ->take(3)
            ->get();

        return view('posts.show', compact('post', 'relatedPosts'));
    }
}
