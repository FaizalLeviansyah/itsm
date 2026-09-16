<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = KnowledgeArticle::with(['category', 'author']);

        // Non-admin users only see published articles
        if (Auth::user()->role === 'user') {
            $query->where('status', 'published');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $articles = $query->orderByDesc('created_at')->paginate(12);
        $categories = Category::where('is_active', true)->get();

        return view('knowledge.index', compact('articles', 'categories'));
    }

    public function show(KnowledgeArticle $article)
    {
        if ($article->status !== 'published' && Auth::user()->role === 'user') {
            abort(404);
        }

        $article->increment('views');
        $article->load(['category', 'author']);

        $related = KnowledgeArticle::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'published')
            ->limit(5)
            ->get();

        return view('knowledge.show', compact('article', 'related'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('knowledge.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,published',
        ]);

        KnowledgeArticle::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'content' => $request->content,
            'category_id' => $request->category_id,
            'author_id' => Auth::id(),
            'status' => $request->status,
        ]);

        return redirect()->route('knowledge.index')->with('success', 'Article created successfully.');
    }

    public function helpful(KnowledgeArticle $article)
    {
        $article->increment('helpful_count');
        return back()->with('success', 'Thank you for your feedback!');
    }
}
