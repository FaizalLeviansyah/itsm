<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Problem;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        $query = Problem::with(['category', 'priority', 'owner']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('problem_number', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            });
        }

        $problems = $query->orderByDesc('created_at')->paginate(15);
        return view('problems.index', compact('problems'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $priorities = Priority::orderBy('sort_order')->get();
        $incidents = Ticket::where('type', 'incident')
            ->whereIn('status', ['open', 'assigned', 'in_progress', 'resolved'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('problems.create', compact('categories', 'priorities', 'incidents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority_id' => 'required|exists:priorities,id',
            'impact' => 'required|in:low,medium,high,critical',
        ]);

        $problem = Problem::create([
            'problem_number' => Problem::generateNumber(),
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'priority_id' => $request->priority_id,
            'owner_id' => Auth::id(),
            'company_id' => Auth::user()->company_id,
            'impact' => $request->impact,
            'identified_at' => now(),
        ]);

        if ($request->filled('incidents')) {
            $problem->tickets()->attach($request->incidents);
            $problem->update(['affected_incidents' => count($request->incidents)]);
        }

        return redirect()->route('problems.show', $problem)->with('success', 'Problem record created successfully.');
    }

    public function show(Problem $problem)
    {
        $problem->load(['category', 'priority', 'owner', 'tickets.priority', 'tickets.requester']);

        $availableIncidents = Ticket::where('type', 'incident')
            ->whereNotIn('id', $problem->tickets->pluck('id'))
            ->whereIn('status', ['open', 'assigned', 'in_progress', 'resolved'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('problems.show', compact('problem', 'availableIncidents'));
    }

    public function update(Request $request, Problem $problem)
    {
        $request->validate([
            'status' => 'nullable|in:open,investigating,known_error,resolved,closed',
            'root_cause' => 'nullable|string',
            'workaround' => 'nullable|string',
            'solution' => 'nullable|string',
        ]);

        $data = $request->only(['status', 'root_cause', 'workaround', 'solution']);

        if ($request->status === 'resolved' && !$problem->resolved_at) {
            $data['resolved_at'] = now();
        }

        $problem->update($data);

        return back()->with('success', 'Problem updated successfully.');
    }

    public function linkIncident(Request $request, Problem $problem)
    {
        $request->validate(['ticket_id' => 'required|exists:tickets,id']);

        $problem->tickets()->syncWithoutDetaching([$request->ticket_id]);
        $problem->update(['affected_incidents' => $problem->tickets()->count()]);

        return back()->with('success', 'Incident linked to problem successfully.');
    }
}
