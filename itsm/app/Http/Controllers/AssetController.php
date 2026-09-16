<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with(['assetCategory', 'assignedUser', 'company']);

        // Non-admin only sees assets assigned to them (as PIC)
        if (!auth()->user()->isAdmin()) {
            $query->where('assigned_to', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('asset_tag', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('serial_number', 'like', "%{$request->search}%");
            });
        }

        $assets = $query->orderByDesc('created_at')->paginate(15);
        $categories = AssetCategory::where('is_active', true)->get();

        return view('assets.index', compact('assets', 'categories'));
    }

    public function create()
    {
        $categories = AssetCategory::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();
        $companies = \App\Models\Company::where('is_active', true)->get();
        return view('assets.create', compact('categories', 'users', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'status' => 'required|in:available,in_use,maintenance,retired,disposed',
            'company_id' => 'nullable|exists:companies,id',
            'description' => 'nullable|string',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'vessel_name' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date',
            'ip_address' => 'nullable|string|max:45',
            'mac_address' => 'nullable|string|max:17',
            'notes' => 'nullable|string',
        ]);

        // Generate asset tag based on company
        $assetTag = Asset::generateAssetTag();
        if (!empty($validated['company_id'])) {
            $company = \App\Models\Company::find($validated['company_id']);
            if ($company) {
                $assetTag = $company->generateAssetTag();
            }
        }

        $asset = Asset::create(array_merge($validated, [
            'asset_tag' => $assetTag,
        ]));

        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => 'Asset created',
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset added successfully.');
    }

    public function show(Asset $asset)
    {
        // Non-admin can only access assets assigned to them
        if (!auth()->user()->isAdmin() && $asset->assigned_to !== auth()->id()) {
            abort(403, 'You do not have access to this asset.');
        }

        $asset->load(['assetCategory', 'assignedUser', 'company', 'histories.user', 'tickets.priority']);

        // Check if there's an active audit that includes this asset
        $activeAuditItem = \App\Models\AssetAuditItem::with('audit')
            ->where('asset_id', $asset->id)
            ->whereHas('audit', function ($q) {
                $q->where('status', 'in_progress');
            })
            ->first();

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('assets.show', compact('asset', 'activeAuditItem', 'users'));
    }

    public function edit(Asset $asset)
    {
        $categories = AssetCategory::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();
        $companies = \App\Models\Company::where('is_active', true)->get();
        return view('assets.edit', compact('asset', 'categories', 'users', 'companies'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'status' => 'nullable|in:available,in_use,maintenance,retired,disposed',
            'company_id' => 'nullable|exists:companies,id',
            'description' => 'nullable|string',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'vessel_name' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date',
            'ip_address' => 'nullable|string|max:45',
            'mac_address' => 'nullable|string|max:17',
            'notes' => 'nullable|string',
        ]);

        $changes = [];
        foreach ($validated as $key => $value) {
            if ((string)($asset->$key ?? '') !== (string)($value ?? '')) {
                $changes[$key] = ['old' => $asset->$key, 'new' => $value];
            }
        }

        $asset->update($validated);

        if (!empty($changes)) {
            // Determine the most specific action label
            $action = 'updated';
            if (array_key_exists('assigned_to', $changes)) {
                $action = 'assigned';
            } elseif (array_key_exists('location', $changes) || array_key_exists('vessel_name', $changes)) {
                $action = 'moved';
            } elseif (array_key_exists('status', $changes)) {
                $action = 'status_changed';
            }

            // Build human-readable description
            $fieldLabels = [
                'name' => 'Name', 'asset_category_id' => 'Category', 'status' => 'Status',
                'company_id' => 'Company', 'description' => 'Description', 'manufacturer' => 'Manufacturer',
                'model' => 'Model', 'serial_number' => 'Serial Number', 'assigned_to' => 'User',
                'location' => 'Location', 'vessel_name' => 'Vessel', 'purchase_date' => 'Purchase Date',
                'purchase_cost' => 'Purchase Cost', 'warranty_expiry' => 'Warranty', 'ip_address' => 'IP Address',
                'mac_address' => 'MAC Address', 'notes' => 'Notes',
            ];

            // Resolve display values for assigned_to (show name not id)
            if (isset($changes['assigned_to'])) {
                $oldUser = $changes['assigned_to']['old'] ? User::find($changes['assigned_to']['old'])?->name : 'None';
                $newUser = $changes['assigned_to']['new'] ? User::find($changes['assigned_to']['new'])?->name : 'None';
                $changes['assigned_to'] = ['old' => $oldUser, 'new' => $newUser];
            }

            $changedFields = implode(', ', array_map(fn($k) => $fieldLabels[$k] ?? $k, array_keys($changes)));
            $description = match($action) {
                'assigned'       => 'User changed',
                'moved'          => 'Location/Vessel transferred',
                'status_changed' => 'Status changed',
                default          => 'Asset updated: ' . $changedFields,
            };

            AssetHistory::create([
                'asset_id'    => $asset->id,
                'user_id'     => Auth::id(),
                'action'      => $action,
                'description' => $description,
                'changes'     => $changes,
            ]);
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset updated successfully.');
    }

    public function transfer(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'location'    => 'nullable|string|max:255',
            'vessel_name' => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $changes = [];

        if (array_key_exists('assigned_to', $validated) &&
            (string)($asset->assigned_to ?? '') !== (string)($validated['assigned_to'] ?? '')) {
            $oldUser = $asset->assigned_to ? User::find($asset->assigned_to)?->name : 'None';
            $newUser = $validated['assigned_to'] ? User::find($validated['assigned_to'])?->name : 'None';
            $changes['assigned_to'] = ['old' => $oldUser, 'new' => $newUser];
        }

        if (array_key_exists('location', $validated) &&
            (string)($asset->location ?? '') !== (string)($validated['location'] ?? '')) {
            $changes['location'] = ['old' => $asset->location, 'new' => $validated['location']];
        }

        if (array_key_exists('vessel_name', $validated) &&
            (string)($asset->vessel_name ?? '') !== (string)($validated['vessel_name'] ?? '')) {
            $changes['vessel_name'] = ['old' => $asset->vessel_name, 'new' => $validated['vessel_name']];
        }

        if (empty($changes)) {
            return back()->with('info', 'No changes were saved.');
        }

        $asset->update([
            'assigned_to' => $validated['assigned_to'],
            'location'    => $validated['location'] ?? $asset->location,
            'vessel_name' => $validated['vessel_name'] ?? $asset->vessel_name,
        ]);

        $description = $validated['notes'] ?: 'Asset transfer';

        AssetHistory::create([
            'asset_id'    => $asset->id,
            'user_id'     => Auth::id(),
            'action'      => 'moved',
            'description' => $description,
            'changes'     => $changes,
        ]);

        return back()->with('success', 'Asset transfer recorded successfully.');
    }

    public function history(Asset $asset)
    {
        if (!auth()->user()->isAdmin() && $asset->assigned_to !== auth()->id()) {
            abort(403, 'You do not have access to this asset.');
        }

        $histories = $asset->histories()->with('user')->paginate(25);
        return view('assets.history', compact('asset', 'histories'));
    }

    public function destroy(Asset $asset)
    {
        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => 'Asset deleted',
        ]);

        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }
}
