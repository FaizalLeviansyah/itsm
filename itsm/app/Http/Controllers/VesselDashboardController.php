<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VesselDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get all vessels from external DB
        $vessels = collect();
        try {
            $vessels = Vessel::select('id', 'vessel_name', 'login_email')->get();
        } catch (\Exception $e) {
            // External DB not available
        }

        // Get vessel stats from local tickets and assets
        $vesselStats = [];
        $vesselNames = Asset::whereNotNull('vessel_name')
            ->select('vessel_name')
            ->distinct()
            ->pluck('vessel_name');

        // Also get from tickets
        $ticketVessels = Ticket::whereNotNull('vessel_name')
            ->select('vessel_name')
            ->distinct()
            ->pluck('vessel_name');

        $allVesselNames = $vesselNames->merge($ticketVessels)->unique();

        foreach ($allVesselNames as $vesselName) {
            $vesselStats[] = [
                'name' => $vesselName,
                'total_assets' => Asset::where('vessel_name', $vesselName)->count(),
                'assets_in_use' => Asset::where('vessel_name', $vesselName)->where('status', 'in_use')->count(),
                'assets_maintenance' => Asset::where('vessel_name', $vesselName)->where('status', 'maintenance')->count(),
                'open_tickets' => Ticket::where('vessel_name', $vesselName)->whereNotIn('status', ['resolved', 'closed'])->count(),
                'total_tickets' => Ticket::where('vessel_name', $vesselName)->count(),
            ];
        }

        // Overall stats
        $stats = [
            'total_vessels' => $allVesselNames->count(),
            'total_vessel_assets' => Asset::whereNotNull('vessel_name')->count(),
            'vessel_open_tickets' => Ticket::whereNotNull('vessel_name')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'vessel_maintenance_assets' => Asset::whereNotNull('vessel_name')->where('status', 'maintenance')->count(),
        ];

        return view('vessels.index', compact('vesselStats', 'stats', 'vessels'));
    }

    public function show(Request $request, string $vesselName)
    {
        $assets = Asset::with(['assetCategory'])
            ->where('vessel_name', $vesselName)
            ->get();

        $tickets = Ticket::with(['priority', 'assignee', 'category'])
            ->where('vessel_name', $vesselName)
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total_assets' => $assets->count(),
            'in_use' => $assets->where('status', 'in_use')->count(),
            'maintenance' => $assets->where('status', 'maintenance')->count(),
            'open_tickets' => Ticket::where('vessel_name', $vesselName)->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('vessels.show', compact('vesselName', 'assets', 'tickets', 'stats'));
    }
}
