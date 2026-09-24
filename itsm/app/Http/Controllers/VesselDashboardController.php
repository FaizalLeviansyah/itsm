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
        $vesselNames = Asset::whereNotNull('vessel_name')
            ->select('vessel_name')
            ->distinct()
            ->pluck('vessel_name');

        // Also get from tickets
        $ticketVessels = Ticket::whereNotNull('vessel_name')
            ->select('vessel_name')
            ->distinct()
            ->pluck('vessel_name');

        $allLocationNames = $vesselNames->merge($ticketVessels)->unique();

        $vesselStats = [];
        $officeStats = [];
        
        $vesselTotals = ['locations' => 0, 'assets' => 0, 'open_tickets' => 0, 'maintenance' => 0];
        $officeTotals = ['locations' => 0, 'assets' => 0, 'open_tickets' => 0, 'maintenance' => 0];

        // Keyword untuk mendeteksi mana yang masuk tab Office
        $officeKeywords = ['pt ', 'shore', 'office', 'cadet', 'new'];

        foreach ($allLocationNames as $locationName) {
            $isOffice = false;
            foreach ($officeKeywords as $keyword) {
                if (stripos($locationName, $keyword) !== false) {
                    $isOffice = true;
                    break;
                }
            }

            $totalAssets = Asset::where('vessel_name', $locationName)->count();
            $assetsInUse = Asset::where('vessel_name', $locationName)->where('status', 'in_use')->count();
            $assetsMaintenance = Asset::where('vessel_name', $locationName)->where('status', 'maintenance')->count();
            $openTickets = Ticket::where('vessel_name', $locationName)->whereNotIn('status', ['resolved', 'closed'])->count();
            $totalTickets = Ticket::where('vessel_name', $locationName)->count();

            $statData = [
                'name' => $locationName,
                'type' => $isOffice ? 'office' : 'vessel', // Penanda untuk ikon dinamis
                'total_assets' => $totalAssets,
                'assets_in_use' => $assetsInUse,
                'assets_maintenance' => $assetsMaintenance,
                'open_tickets' => $openTickets,
                'total_tickets' => $totalTickets,
            ];

            if ($isOffice) {
                $officeStats[] = $statData;
                $officeTotals['locations']++;
                $officeTotals['assets'] += $totalAssets;
                $officeTotals['open_tickets'] += $openTickets;
                $officeTotals['maintenance'] += $assetsMaintenance;
            } else {
                $vesselStats[] = $statData;
                $vesselTotals['locations']++;
                $vesselTotals['assets'] += $totalAssets;
                $vesselTotals['open_tickets'] += $openTickets;
                $vesselTotals['maintenance'] += $assetsMaintenance;
            }
        }

        // Gabungkan seluruh data untuk tab 'All'
        $allStats = array_merge($vesselStats, $officeStats);
        $allTotals = [
            'locations' => $vesselTotals['locations'] + $officeTotals['locations'],
            'assets' => $vesselTotals['assets'] + $officeTotals['assets'],
            'open_tickets' => $vesselTotals['open_tickets'] + $officeTotals['open_tickets'],
            'maintenance' => $vesselTotals['maintenance'] + $officeTotals['maintenance'],
        ];

        return view('vessels.index', compact(
            'allStats', 'vesselStats', 'officeStats', 
            'allTotals', 'vesselTotals', 'officeTotals', 
            'vessels'
        ));
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