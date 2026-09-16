<?php

namespace App\Http\Controllers;

use App\Services\SocIntegrationService;
use Illuminate\Http\Request;

class SocSyncController extends Controller
{
    protected SocIntegrationService $socService;

    public function __construct(SocIntegrationService $socService)
    {
        $this->socService = $socService;
    }

    /**
     * Sync all SOC endpoints to assets
     */
    public function sync(Request $request)
    {
        $result = $this->socService->syncAll(auth()->id());

        if (!$result['success']) {
            return back()->with('error', 'SOC Sync failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $message = sprintf(
            'SOC Sync completed: %d created, %d updated, %d failed (Total: %d endpoints)',
            $result['created'],
            $result['updated'],
            $result['failed'],
            $result['total']
        );

        return back()->with('success', $message);
    }

    /**
     * API endpoint for sync status
     */
    public function status()
    {
        $result = $this->socService->fetchEndpoints();

        return response()->json([
            'success' => $result['success'],
            'endpoint_count' => $result['count'] ?? 0,
            'error' => $result['error'] ?? null,
        ]);
    }
}
