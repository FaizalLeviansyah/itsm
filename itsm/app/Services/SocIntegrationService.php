<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocIntegrationService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.soc.api_url');
        $this->apiKey = config('services.soc.api_key');
    }

    /**
     * Fetch all endpoints from SOC API
     */
    public function fetchEndpoints(): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get($this->apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['data'] ?? [],
                    'count' => $data['count'] ?? 0,
                ];
            }

            Log::error('SOC API Error', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'error' => 'API request failed: ' . $response->status(), 'data' => []];
        } catch (\Exception $e) {
            Log::error('SOC API Exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Sync all endpoints to assets
     */
    public function syncAll(?int $userId = null): array
    {
        $result = $this->fetchEndpoints();
        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error'], 'created' => 0, 'updated' => 0, 'failed' => 0];
        }

        $created = 0; $updated = 0; $failed = 0;

        foreach ($result['data'] as $endpoint) {
            try {
                $syncResult = $this->syncEndpoint($endpoint, $userId);
                $syncResult['action'] === 'created' ? $created++ : $updated++;
            } catch (\Exception $e) {
                Log::error('SOC Sync Failed', ['endpoint_id' => $endpoint['id'] ?? 'unknown', 'error' => $e->getMessage()]);
                $failed++;
            }
        }

        return ['success' => true, 'created' => $created, 'updated' => $updated, 'failed' => $failed, 'total' => count($result['data'])];
    }

    /**
     * Sync a single endpoint to asset
     */
    public function syncEndpoint(array $endpoint, ?int $userId = null): array
    {
        $asset = Asset::where('soc_endpoint_id', $endpoint['id'])
            ->orWhere('hostname', $endpoint['hostname'])
            ->first();

        $isNew = !$asset;
        if ($isNew) {
            $asset = new Asset();
            $asset->asset_tag = 'SOC-' . strtoupper($endpoint['hostname'] ?? str_pad($endpoint['id'], 5, '0', STR_PAD_LEFT));
        }

        $oldData = $isNew ? [] : $asset->toArray();
        $brandInfo = $this->parsePcBrand($endpoint['pc_brand'] ?? '');

        $asset->soc_endpoint_id = $endpoint['id'];
        $asset->hostname = $endpoint['hostname'];
        $asset->name = $this->generateAssetName($endpoint, $brandInfo);
        $asset->vessel_name = $endpoint['vessel_name'] ?? $endpoint['assigned_vessel'] ?? null;
        $asset->manufacturer = $brandInfo['manufacturer'];
        $asset->model = $brandInfo['model'];
        $asset->serial_number = $brandInfo['serial_number'] ?? $asset->serial_number;
        $asset->pc_brand = $endpoint['pc_brand'] ?? null;
        $asset->pc_specs = $endpoint['pc_specs'] ?? null;
        $asset->antivirus_status = $endpoint['antivirus'] ?? null;
        $asset->usb_status = $endpoint['usb_status'] ?? null;
        $asset->windows_update_status = $endpoint['windows_update'] ?? null;
        $asset->soc_status = $endpoint['status'] ?? 'unknown';
        $asset->soc_last_seen = isset($endpoint['last_seen']) ? \Carbon\Carbon::parse($endpoint['last_seen']) : null;
        $asset->installed_apps = $endpoint['installed_apps'] ?? null;
        $asset->paired_hardware = $endpoint['paired_hardware'] ?? null;

        if (!$asset->asset_category_id) {
            $asset->asset_category_id = $this->getOrCreateCategory();
        }
        if ($isNew) {
            $asset->status = 'in_use';
        }
        
        // --- LOGIK BARU: OTOMATISASI PEMBUATAN USER ---
        if (!empty($endpoint['alias'])) {
            // Coba cari user berdasarkan namanya
            $user = \App\Models\User::where('name', $endpoint['alias'])->first();
            
            // Jika user belum ada di ITSM, buatkan secara otomatis
            if (!$user) {
                // Buat email dummy yang unik berdasarkan nama
                $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $endpoint['alias']));
                $emailDummy = $cleanName . '@soc-sync.local';
                
                // Pastikan email tidak duplikat
                $existingEmail = \App\Models\User::where('email', $emailDummy)->first();
                if (!$existingEmail) {
                    $user = \App\Models\User::create([
                        'name' => $endpoint['alias'],
                        'email' => $emailDummy,
                        'password' => bcrypt('password123'), // Password dummy default
                    ]);
                }
            }
            
            // Assign ID user ke asset
            if ($user) {
                $asset->assigned_to = $user->id;
            }
        }
        // ----------------------------------------------

        $asset->soc_synced_at = now();
        $asset->save();

        if ($userId) {
            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => $userId,
                'action' => $isNew ? 'soc_created' : 'soc_synced',
                'description' => $isNew ? 'Asset created from SOC integration' : 'Asset synced from SOC',
                'changes' => $isNew ? [] : $this->getChanges($oldData, $asset->toArray()),
            ]);
        }

        return ['action' => $isNew ? 'created' : 'updated', 'asset' => $asset];
    }

    protected function generateAssetName(array $endpoint, array $brandInfo): string
    {
        $parts = [];
        if ($brandInfo['manufacturer'] && $brandInfo['model']) {
            $parts[] = $brandInfo['manufacturer'] . ' ' . $brandInfo['model'];
        } elseif (!empty($endpoint['hostname'])) {
            $parts[] = $endpoint['hostname'];
        }
        if (!empty($endpoint['alias'])) {
            $parts[] = '(' . $endpoint['alias'] . ')';
        }
        return implode(' ', $parts) ?: 'SOC Endpoint #' . $endpoint['id'];
    }

    protected function parsePcBrand(?string $pcBrand): array
    {
        if (empty($pcBrand)) {
            return ['manufacturer' => null, 'model' => null, 'serial_number' => null];
        }
        $result = ['manufacturer' => null, 'model' => null, 'serial_number' => null];

        if (preg_match('/\(SN:\s*([^)]+)\)/', $pcBrand, $matches)) {
            $result['serial_number'] = trim($matches[1]);
            $pcBrand = preg_replace('/\(SN:\s*[^)]+\)/', '', $pcBrand);
        }
        $pcBrand = trim(preg_replace('/\[v[\d.]+\]\s*/', '', $pcBrand));

        $manufacturers = ['Acer', 'HP', 'Dell', 'Lenovo', 'Asus', 'MSI', 'Apple', 'Toshiba', 'Samsung', 'Microsoft'];
        foreach ($manufacturers as $mfr) {
            if (stripos($pcBrand, $mfr) !== false) {
                $result['manufacturer'] = $mfr;
                $result['model'] = trim(preg_replace('/^' . preg_quote($mfr, '/') . '\s*/i', '', $pcBrand));
                break;
            }
        }
        if (!$result['manufacturer'] && $pcBrand) {
            $parts = explode(' ', $pcBrand, 2);
            $result['manufacturer'] = $parts[0] ?? null;
            $result['model'] = $parts[1] ?? null;
        }
        return $result;
    }

    protected function getOrCreateCategory(): int
    {
        $category = AssetCategory::where('slug', 'computer')
            ->orWhere('slug', 'laptop')
            ->orWhere('name', 'like', '%computer%')
            ->orWhere('name', 'like', '%laptop%')
            ->first();

        if (!$category) {
            $category = AssetCategory::create([
                'name' => 'Computer / Laptop',
                'slug' => 'computer-laptop',
                'description' => 'Desktop computers and laptops',
                'icon' => 'fas fa-laptop',
                'is_active' => true,
            ]);
        }
        return $category->id;
    }

    protected function getChanges(array $old, array $new): array
    {
        $changes = [];
        $trackFields = ['hostname', 'antivirus_status', 'usb_status', 'windows_update_status', 'soc_status', 'pc_brand', 'pc_specs', 'vessel_name'];
        foreach ($trackFields as $field) {
            if (($old[$field] ?? null) !== ($new[$field] ?? null)) {
                $changes[$field] = ['old' => $old[$field] ?? null, 'new' => $new[$field] ?? null];
            }
        }
        return $changes;
    }
}