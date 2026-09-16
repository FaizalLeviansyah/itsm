<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return view('search.index', ['q' => $q, 'results' => collect()]);
        }

        $tickets = Ticket::with(['priority', 'requester'])
            ->where(function ($query) use ($q) {
                $query->where('ticket_number', 'like', "%{$q}%")
                      ->orWhere('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->when(Auth::user()->role === 'user', fn($query) => $query->where('requester_id', Auth::id()))
            ->limit(10)->get();

        $assets = Asset::with('assetCategory')
            ->where(function ($query) use ($q) {
                $query->where('asset_tag', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%")
                      ->orWhere('serial_number', 'like', "%{$q}%");
            })
            ->limit(10)->get();

        $users = collect();
        if (in_array(Auth::user()->role, ['admin', 'technician'])) {
            $users = User::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            })->limit(5)->get();
        }

        $articles = KnowledgeArticle::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")->orWhere('content', 'like', "%{$q}%");
            })->limit(5)->get();

        return view('search.index', compact('q', 'tickets', 'assets', 'users', 'articles'));
    }
}
