<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AutoAssignRule;
use App\Models\CannedResponse;
use App\Models\Category;
use App\Models\EscalationRule;
use App\Models\Holiday;
use App\Models\MaintenanceSchedule;
use App\Models\Priority;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    // User Management
    public function users(Request $request)
    {
        $query = User::query();

        // Implementasi logika filter dropdown Office/Vessel
        if ($request->has('user_type') && $request->user_type != '') {
            if ($request->user_type == 'vessel') {
                $query->where('department', 'Vessel');
            } elseif ($request->user_type == 'office') {
                $query->where(function($q) {
                    $q->where('department', '!=', 'Vessel')->orWhereNull('department');
                });
            }
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20);

        // Count external users not yet synced
        $unsyncedCount = 0;
        try {
            $existingEmails = User::pluck('email')->toArray();
            $unsyncedCount = \App\Models\Employee::where('is_active', 1)
                ->whereNotNull('email_work')
                ->where('email_work', '!=', '')
                ->whereNotIn('email_work', $existingEmails)
                ->count();
        } catch (\Exception $e) {}

        return view('admin.settings.users', compact('users', 'unsyncedCount'));
    }

    public function syncUsers()
    {
        try {
            Artisan::call('sync:employees');
            return redirect()->back()->with('success', 'Employees (Office) synchronized successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to sync employees: ' . $e->getMessage());
        }
    }

    public function syncVessels()
{
    try {
        $apiKey = env('MASTER_VESSEL_API_KEY', 'OJABxyAuodPWhds3S0YsPlbu40DTRAwe');
        $vesselApiUrl = env('MASTER_VESSEL_API_URL', 'http://api.amarin.biz.id/api/v1/data/db_master_ship/vessel');
        
        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Accept' => 'application/json',
        ])->get($vesselApiUrl, [
            'page' => 1,
            'per_page' => 500,
            'sort' => '-id',
        ]);

        if ($response->successful()) {
            $vessels = $response->json()['data'] ?? [];
            foreach ($vessels as $vessel) {
                $vesselName = $vessel['vessel_name'] ?? $vessel['name'] ?? null;
                if (!$vesselName) continue;

                $email = $vessel['login_email'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $vesselName)) . '@vessel.amarin.biz.id';

                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $vesselName,
                        'password' => $vessel['login_password'] ?? $vessel['password'] ?? bcrypt('vesselpassword'),
                        'role' => 'user',
                        'department' => 'Vessel',
                        'job_title' => 'Vessel',
                        'position' => 'Vessel - ' . $vesselName,
                        'source' => 'vessel_api',
                        'source_id' => $vessel['id'] ?? null,
                    ]
                );
            }
            return redirect()->back()->with('success', 'Vessels synchronized successfully.');
        }

        return redirect()->back()->with('error', 'Failed to fetch vessel data from API. Status: ' . $response->status());

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to sync vessels: ' . $e->getMessage());
    }
}

    public function updateUserRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,technician,user']);
        $user->update(['role' => $request->role]);
        return back()->with('success', "{$user->name}'s role changed to {$request->role}.");
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} {$status} successfully.");
    }

    // Category Management
    public function categories()
    {
        $categories = Category::with('subCategories')->orderBy('sort_order')->get();
        return view('admin.settings.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?? '#3B82F6',
        ]);
        return back()->with('success', 'Category added successfully.');
    }

    public function storeSubCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);
        SubCategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);
        return back()->with('success', 'Sub-category added successfully.');
    }

    // Priority Management
    public function priorities()
    {
        $priorities = Priority::orderBy('sort_order')->get();
        return view('admin.settings.priorities', compact('priorities'));
    }

    public function storePriority(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sla_hours' => 'required|integer|min:1',
            'response_hours' => 'required|integer|min:1',
        ]);
        Priority::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#6B7280',
            'sla_hours' => $request->sla_hours,
            'response_hours' => $request->response_hours,
        ]);
        return back()->with('success', 'Priority added successfully.');
    }

    // Asset Category Management
    public function assetCategories()
    {
        $categories = AssetCategory::withCount('assets')->get();
        return view('admin.settings.asset-categories', compact('categories'));
    }

    public function storeAssetCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        AssetCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
        ]);
        return back()->with('success', 'Asset category added successfully.');
    }

    // Holiday Management (SLA Calendar)
    public function holidays()
    {
        $holidays = Holiday::orderBy('date')->get();
        return view('admin.settings.holidays', compact('holidays'));
    }

    public function storeHoliday(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        return back()->with('success', 'Holiday added successfully.');
    }

    public function destroyHoliday(Holiday $holiday)
    {
        $holiday->delete();
        return back()->with('success', 'Holiday deleted successfully.');
    }

    // Escalation Rules
    public function escalationRules()
    {
        $rules = EscalationRule::with(['priority', 'escalateTo'])->orderBy('priority_id')->orderBy('level')->get();
        $priorities = Priority::all();
        $technicians = User::whereIn('role', ['technician', 'admin'])->where('is_active', true)->get();
        return view('admin.settings.escalation', compact('rules', 'priorities', 'technicians'));
    }

    public function storeEscalationRule(Request $request)
    {
        $request->validate([
            'priority_id' => 'required|exists:priorities,id',
            'escalation_minutes' => 'required|integer|min:5',
            'escalate_to' => 'required|exists:users,id',
            'level' => 'required|integer|min:1|max:5',
        ]);

        EscalationRule::create($request->only(['priority_id', 'escalation_minutes', 'escalate_to', 'level']));
        return back()->with('success', 'Escalation rule added successfully.');
    }

    // Auto-Assign Rules
    public function autoAssign()
    {
        $rules = AutoAssignRule::with(['category', 'subCategory', 'company', 'assignee'])->orderBy('category_id')->get();
        $categories = Category::with('subCategories')->where('is_active', true)->get();
        $technicians = User::whereIn('role', ['technician', 'admin'])->where('is_active', true)->get();
        $companies = \App\Models\Company::where('is_active', true)->get();
        return view('admin.settings.auto-assign', compact('rules', 'categories', 'technicians', 'companies'));
    }

    public function storeAutoAssign(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'assign_to' => 'required|exists:users,id',
        ]);

        AutoAssignRule::create($request->only(['category_id', 'sub_category_id', 'company_id', 'vessel_name', 'source_type', 'assign_to']));
        return back()->with('success', 'Auto-assign rule added successfully.');
    }

    // Canned Responses
    public function cannedResponses()
    {
        $responses = CannedResponse::with(['category', 'creator'])->orderByDesc('usage_count')->get();
        $categories = Category::where('is_active', true)->get();
        return view('admin.settings.canned-responses', compact('responses', 'categories'));
    }

    public function storeCannedResponse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        CannedResponse::create([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'created_by' => Auth::id(),
        ]);
        return back()->with('success', 'Canned response added successfully.');
    }

    // Maintenance Schedules
    public function maintenance()
    {
        $schedules = MaintenanceSchedule::with(['asset', 'assignee'])->orderBy('next_due_date')->get();
        $assets = Asset::whereIn('status', ['in_use', 'available'])->orderBy('name')->get();
        $technicians = User::whereIn('role', ['technician', 'admin'])->where('is_active', true)->get();
        return view('admin.settings.maintenance', compact('schedules', 'assets', 'technicians'));
    }

    public function storeMaintenance(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'asset_id' => 'required|exists:assets,id',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,semi_annual,annual',
            'next_due_date' => 'required|date',
        ]);

        $asset = Asset::find($request->asset_id);

        MaintenanceSchedule::create([
            'title' => $request->title,
            'description' => $request->description,
            'asset_id' => $request->asset_id,
            'assigned_to' => $request->assigned_to,
            'company_id' => $asset->company_id,
            'frequency' => $request->frequency,
            'next_due_date' => $request->next_due_date,
            'auto_create_ticket' => $request->boolean('auto_create_ticket', true),
        ]);
        return back()->with('success', 'Maintenance schedule added successfully.');
    }

    // Delete methods
    public function destroyCategory(Category $category)
    {
        $category->subCategories()->delete();
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    public function destroySubCategory(\App\Models\SubCategory $subCategory)
    {
        $subCategory->delete();
        return back()->with('success', 'Sub-category deleted.');
    }

    public function destroyPriority(Priority $priority)
    {
        $priority->delete();
        return back()->with('success', 'Priority deleted.');
    }

    public function destroyAssetCategory(AssetCategory $assetCategory)
    {
        $assetCategory->delete();
        return back()->with('success', 'Asset category deleted.');
    }

    public function destroyEscalationRule(EscalationRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Escalation rule deleted.');
    }

    public function destroyAutoAssign(AutoAssignRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Auto-assign rule deleted.');
    }

    public function destroyCannedResponse(CannedResponse $response)
    {
        $response->delete();
        return back()->with('success', 'Canned response deleted.');
    }

    public function destroyMaintenance(MaintenanceSchedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Maintenance schedule deleted.');
    }
}