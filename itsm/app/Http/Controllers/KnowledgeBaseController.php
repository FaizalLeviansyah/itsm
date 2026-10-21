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
        $query = KnowledgeArticle::with(['category', 'author'])
            ->where('status', 'published');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $articles = $query->orderByDesc('created_at')->get();
        $categories = Category::where('is_active', true)->get();

        // Tentukan Ebook PDF mana yang sedang aktif dibuka di viewer (Default: Ebook Terbaru)
        $activeArticle = null;
        if ($request->filled('article_id')) {
            $activeArticle = KnowledgeArticle::find($request->article_id);
        }
        if (!$activeArticle) {
            $activeArticle = $articles->first();
        }

        return view('knowledge.index', compact('articles', 'categories', 'activeArticle'));
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
            'category_id' => 'required|exists:categories,id',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // Mendukung file PDF ebook hingga 50MB
            'status' => 'required|in:draft,published',
            'content' => 'nullable|string',
        ]);

        $pdfPath = null;
        if ($request->hasFile('pdf_file')) {
            $pdfPath = $request->file('pdf_file')->store('knowledge/ebooks', 'public');
        }

        KnowledgeArticle::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'content' => $request->content ?? 'Digital Literature Ebook & Manual Book Documentation.',
            'pdf_path' => $pdfPath,
            'category_id' => $request->category_id,
            'author_id' => Auth::id(),
            'status' => $request->status,
        ]);

        return redirect()->route('knowledge.index')->with('success', 'Ebook / Literatur PDF berhasil diimpor dan siap dibaca.');
    }

    public function helpful(KnowledgeArticle $article)
    {
        $article->increment('helpful_count');
        return back()->with('success', 'Thank you for your feedback!');
    }
}
