<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAudit;
use App\Models\AssetAuditItem;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetAuditController extends Controller
{
    public function index()
    {
        $audits = AssetAudit::with(['auditor', 'company'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('audits.index', compact('audits'));
    }

    public function create()
    {
        $companies = Company::where('is_active', true)->get();
        return view('audits.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'scheduled_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'vessel_name' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        // Get assets based on filter
        $assetQuery = Asset::query();
        if ($request->filled('company_id')) {
            $assetQuery->where('company_id', $request->company_id);
        }
        if ($request->filled('location')) {
            $assetQuery->where('location', 'like', "%{$request->location}%");
        }
        if ($request->filled('vessel_name')) {
            $assetQuery->where('vessel_name', $request->vessel_name);
        }

        $assets = $assetQuery->whereIn('status', ['available', 'in_use', 'maintenance'])->get();

        $audit = AssetAudit::create([
            'audit_number' => AssetAudit::generateNumber(),
            'title' => $request->title,
            'description' => $request->description,
            'auditor_id' => Auth::id(),
            'company_id' => $request->company_id,
            'status' => 'planned',
            'scheduled_date' => $request->scheduled_date,
            'location' => $request->location,
            'vessel_name' => $request->vessel_name,
            'total_assets' => $assets->count(),
        ]);

        // Create audit items for each asset
        foreach ($assets as $asset) {
            AssetAuditItem::create([
                'asset_audit_id' => $audit->id,
                'asset_id' => $asset->id,
                'condition' => 'good',
                'scan_status' => 'pending',
            ]);
        }

        return redirect()->route('audits.show', $audit)->with('success', "Audit created with {$assets->count()} assets.");
    }

    public function show(AssetAudit $audit)
    {
        $audit->load(['auditor', 'company', 'items.asset.assetCategory']);

        $stats = [
            'total' => $audit->items->count(),
            'scanned' => $audit->items->whereIn('scan_status', ['scanned', 'manual'])->count(),
            'pending' => $audit->items->where('scan_status', 'pending')->count(),
            'good' => $audit->items->where('condition', 'good')->whereIn('scan_status', ['scanned', 'manual'])->count(),
            'damaged' => $audit->items->whereIn('condition', ['damaged', 'poor'])->count(),
            'missing' => $audit->items->where('condition', 'missing')->count(),
        ];

        return view('audits.show', compact('audit', 'stats'));
    }

    public function start(AssetAudit $audit)
    {
        $audit->update(['status' => 'in_progress']);
        return back()->with('success', 'Audit started.');
    }

    public function scanAsset(Request $request, AssetAudit $audit)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'condition' => 'required|in:good,fair,poor,damaged,missing',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = $audit->items()->where('asset_id', $request->asset_id)->first();

        if (!$item) {
            return response()->json(['error' => 'Asset is not part of this audit'], 404);
        }

        $item->update([
            'condition' => $request->condition,
            'scan_status' => 'scanned',
            'scanned_at' => now(),
            'scanned_by' => Auth::id(),
            'notes' => $request->notes,
        ]);

        // Update audit totals
        $this->updateAuditTotals($audit);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Asset scanned successfully.']);
        }

        return back()->with('success', "Asset {$item->asset->asset_tag} scanned successfully.");
    }

    public function updateItem(Request $request, AssetAudit $audit, AssetAuditItem $item)
    {
        $request->validate([
            'condition' => 'required|in:good,fair,poor,damaged,missing',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->update([
            'condition' => $request->condition,
            'scan_status' => 'manual',
            'scanned_at' => now(),
            'scanned_by' => Auth::id(),
            'notes' => $request->notes,
        ]);

        $this->updateAuditTotals($audit);
        return back()->with('success', 'Audit item updated successfully.');
    }

    public function complete(AssetAudit $audit)
    {
        $this->updateAuditTotals($audit);
        $audit->update([
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        return back()->with('success', 'Audit completed.');
    }

    // QR Scan endpoint - when someone scans QR during audit
    public function qrScan(Request $request, AssetAudit $audit, Asset $asset)
    {
        $item = $audit->items()->where('asset_id', $asset->id)->first();

        if (!$item) {
            return back()->with('error', 'This asset is not part of the current audit.');
        }

        return view('audits.scan-result', compact('audit', 'asset', 'item'));
    }

    private function updateAuditTotals(AssetAudit $audit): void
    {
        $items = $audit->items()->get();
        $audit->update([
            'found_assets' => $items->whereIn('scan_status', ['scanned', 'manual'])->where('condition', '!=', 'missing')->count(),
            'missing_assets' => $items->where('condition', 'missing')->count(),
            'damaged_assets' => $items->whereIn('condition', ['damaged', 'poor'])->count(),
        ]);
    }
}
