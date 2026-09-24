<?php
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AssetAuditController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetImportController;
use App\Http\Controllers\AssetStickerController;
use App\Http\Controllers\SocSyncController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceCatalogController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VesselDashboardController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/', fn() => redirect('/login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// SSO Callback - menerima token dari portal (no auth required)
Route::get('/sso/callback', [\App\Http\Controllers\SsoCallbackController::class, 'handle'])->name('sso.callback');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Tickets
    Route::resource('tickets', TicketController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/comment', [TicketController::class, 'addComment'])->name('tickets.comment');
    Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    
    // ---> TAMBAHKAN BARIS INI DI SINI <---
    Route::post('/tickets/{ticket}/rate-and-close', [TicketController::class, 'rateAndClose'])->name('tickets.rateAndClose');
    Route::get('/api/subcategories/{categoryId}', [TicketController::class, 'getSubCategories'])->name('api.subcategories');

    // Ratings
    Route::post('/tickets/{ticket}/rate', [RatingController::class, 'store'])->name('tickets.rate');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::get('/api/notifications/count', [NotificationController::class, 'unreadCount'])->name('api.notifications.count');

    // Service Catalog
    Route::get('/services', [ServiceCatalogController::class, 'index'])->name('services.index');

    // Assets
    Route::resource('assets', AssetController::class);
    Route::get('/assets/{asset}/history', [AssetController::class, 'history'])->name('assets.history');
    Route::post('/assets/{asset}/transfer', [AssetController::class, 'transfer'])->name('assets.transfer');
    Route::get('/assets-import', [AssetImportController::class, 'showForm'])->name('assets.import');
    Route::post('/assets-import', [AssetImportController::class, 'import'])->name('assets.import.process');
    Route::get('/assets-import/template', [AssetImportController::class, 'downloadTemplate'])->name('assets.import.template');
    Route::get('/assets/{asset}/sticker', [AssetStickerController::class, 'preview'])->name('assets.sticker');
    Route::get('/assets/{asset}/sticker/print', [AssetStickerController::class, 'print'])->name('assets.sticker.print');
    Route::post('/assets/sticker/batch', [AssetStickerController::class, 'printBatch'])->name('assets.sticker.batch');
    Route::get('/assets/{asset}/qr.png', [QrCodeController::class, 'asset'])->name('assets.qr');
    
    // SOC Integration
    Route::post('/assets/soc-sync', [SocSyncController::class, 'sync'])->name('assets.soc.sync')->middleware('can:manageSettings');
    Route::get('/assets/soc-status', [SocSyncController::class, 'status'])->name('assets.soc.status');

    // Asset Audit
    Route::get('/audits', [AssetAuditController::class, 'index'])->name('audits.index');
    Route::get('/audits/create', [AssetAuditController::class, 'create'])->name('audits.create');
    Route::post('/audits', [AssetAuditController::class, 'store'])->name('audits.store');
    Route::get('/audits/{audit}', [AssetAuditController::class, 'show'])->name('audits.show');
    Route::post('/audits/{audit}/start', [AssetAuditController::class, 'start'])->name('audits.start');
    Route::post('/audits/{audit}/complete', [AssetAuditController::class, 'complete'])->name('audits.complete');
    Route::post('/audits/{audit}/scan', [AssetAuditController::class, 'scanAsset'])->name('audits.scan');
    Route::post('/audits/{audit}/items/{item}', [AssetAuditController::class, 'updateItem'])->name('audits.updateItem');
    Route::get('/audits/{audit}/qr/{asset}', [AssetAuditController::class, 'qrScan'])->name('audits.qrScan');

    // Knowledge Base
    Route::get('/knowledge', [KnowledgeBaseController::class, 'index'])->name('knowledge.index');
    Route::get('/knowledge/create', [KnowledgeBaseController::class, 'create'])->name('knowledge.create')->middleware('can:manageTickets');
    Route::post('/knowledge', [KnowledgeBaseController::class, 'store'])->name('knowledge.store')->middleware('can:manageTickets');
    Route::get('/knowledge/{article}', [KnowledgeBaseController::class, 'show'])->name('knowledge.show');
    Route::post('/knowledge/{article}/helpful', [KnowledgeBaseController::class, 'helpful'])->name('knowledge.helpful');

    // Approvals (Admin only)
    Route::middleware('can:manageSettings')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // Reports & Exports (Admin/Technician only)
    Route::middleware('can:viewReports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/export/tickets/excel', [ExportController::class, 'exportTicketsExcel'])->name('export.tickets.excel');
        Route::get('/export/tickets/pdf', [ExportController::class, 'exportTicketsPdf'])->name('export.tickets.pdf');
        Route::get('/export/performance/excel', [ExportController::class, 'exportPerformanceExcel'])->name('export.performance.excel');
    });

    // Problem Management (Admin/Technician only)
    Route::middleware('can:viewReports')->group(function () {
        Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
        Route::get('/problems/create', [ProblemController::class, 'create'])->name('problems.create');
        Route::post('/problems', [ProblemController::class, 'store'])->name('problems.store');
        Route::get('/problems/{problem}', [ProblemController::class, 'show'])->name('problems.show');
        Route::put('/problems/{problem}', [ProblemController::class, 'update'])->name('problems.update');
        Route::post('/problems/{problem}/link', [ProblemController::class, 'linkIncident'])->name('problems.link');
    });

    // Vessel Dashboard (Admin/Technician only)
    Route::middleware('can:viewReports')->group(function () {
        Route::get('/vessels', [VesselDashboardController::class, 'index'])->name('vessels.index');
        Route::get('/vessels/{vesselName}', [VesselDashboardController::class, 'show'])->name('vessels.show');
    });

    // Admin Settings
    Route::prefix('admin')->name('admin.')->middleware('can:manageSettings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');
        Route::post('/settings/users/sync', [SettingsController::class, 'syncUsers'])->name('settings.users.sync');
        Route::post('/settings/users/sync-vessels', [SettingsController::class, 'syncVessels'])->name('settings.users.sync-vessels'); // Rute khusus sinkronisasi kapal
        Route::put('/settings/users/{user}/role', [SettingsController::class, 'updateUserRole'])->name('settings.users.role');
        Route::put('/settings/users/{user}/toggle', [SettingsController::class, 'toggleUserStatus'])->name('settings.users.toggle');
        Route::get('/settings/categories', [SettingsController::class, 'categories'])->name('settings.categories');
        Route::post('/settings/categories', [SettingsController::class, 'storeCategory'])->name('settings.categories.store');
        Route::post('/settings/sub-categories', [SettingsController::class, 'storeSubCategory'])->name('settings.subcategories.store');
        Route::get('/settings/priorities', [SettingsController::class, 'priorities'])->name('settings.priorities');
        Route::post('/settings/priorities', [SettingsController::class, 'storePriority'])->name('settings.priorities.store');
        Route::get('/settings/asset-categories', [SettingsController::class, 'assetCategories'])->name('settings.asset-categories');
        Route::post('/settings/asset-categories', [SettingsController::class, 'storeAssetCategory'])->name('settings.asset-categories.store');
        Route::get('/settings/holidays', [SettingsController::class, 'holidays'])->name('settings.holidays');
        Route::post('/settings/holidays', [SettingsController::class, 'storeHoliday'])->name('settings.holidays.store');
        Route::delete('/settings/holidays/{holiday}', [SettingsController::class, 'destroyHoliday'])->name('settings.holidays.destroy');
        Route::get('/settings/escalation', [SettingsController::class, 'escalationRules'])->name('settings.escalation');
        Route::post('/settings/escalation', [SettingsController::class, 'storeEscalationRule'])->name('settings.escalation.store');
        
        // Company Management Routes (Single Source of Truth & Sync API)
        Route::get('/settings/companies', [CompanyController::class, 'index'])->name('settings.companies');
        Route::post('/settings/companies/sync', [CompanyController::class, 'syncApi'])->name('settings.companies.sync');
        Route::put('/settings/companies/{company}/logo', [CompanyController::class, 'updateLogo'])->name('settings.companies.update-logo');

        Route::get('/settings/auto-assign', [SettingsController::class, 'autoAssign'])->name('settings.auto-assign');
        Route::post('/settings/auto-assign', [SettingsController::class, 'storeAutoAssign'])->name('settings.auto-assign.store');
        Route::get('/settings/canned-responses', [SettingsController::class, 'cannedResponses'])->name('settings.canned-responses');
        Route::post('/settings/canned-responses', [SettingsController::class, 'storeCannedResponse'])->name('settings.canned-responses.store');
        Route::get('/settings/maintenance', [SettingsController::class, 'maintenance'])->name('settings.maintenance');
        Route::post('/settings/maintenance', [SettingsController::class, 'storeMaintenance'])->name('settings.maintenance.store');

        // Delete routes for settings items
        Route::delete('/settings/categories/{category}', [SettingsController::class, 'destroyCategory'])->name('settings.categories.destroy');
        Route::delete('/settings/sub-categories/{subCategory}', [SettingsController::class, 'destroySubCategory'])->name('settings.subcategories.destroy');
        Route::delete('/settings/priorities/{priority}', [SettingsController::class, 'destroyPriority'])->name('settings.priorities.destroy');
        Route::delete('/settings/asset-categories/{assetCategory}', [SettingsController::class, 'destroyAssetCategory'])->name('settings.asset-categories.destroy');
        Route::delete('/settings/escalation/{rule}', [SettingsController::class, 'destroyEscalationRule'])->name('settings.escalation.destroy');
        Route::delete('/settings/auto-assign/{rule}', [SettingsController::class, 'destroyAutoAssign'])->name('settings.auto-assign.destroy');
        Route::delete('/settings/canned-responses/{response}', [SettingsController::class, 'destroyCannedResponse'])->name('settings.canned-responses.destroy');
        Route::delete('/settings/maintenance/{schedule}', [SettingsController::class, 'destroyMaintenance'])->name('settings.maintenance.destroy');
    });

    // Bulk Ticket Actions
    Route::post('/tickets/bulk-action', [TicketController::class, 'bulkAction'])->name('tickets.bulk')->middleware('can:manageTickets');
});