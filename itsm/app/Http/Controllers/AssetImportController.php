<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetHistory;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AssetImportController extends Controller
{
    public function showForm()
    {
        return view('assets.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:5120',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $file = $request->file('file');
        $company = $request->company_id ? Company::find($request->company_id) : null;
        $categories = AssetCategory::pluck('id', 'name')->toArray();

        $data = Excel::toArray(null, $file);
        if (empty($data) || empty($data[0])) {
            return back()->with('error', 'File is empty or format is invalid.');
        }

        $rows = $data[0];
        $headers = array_map('strtolower', array_map('trim', $rows[0]));
        $imported = 0;
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = array_combine($headers, $rows[$i] ?? []);
            if (empty($row['name'] ?? null)) continue;

            $categoryName = $row['category'] ?? $row['kategori'] ?? '';
            $categoryId = $categories[$categoryName] ?? (AssetCategory::first()?->id ?? 1);

            $assetTag = Asset::generateAssetTag();
            if ($company) {
                $assetTag = $company->generateAssetTag();
            }

            try {
                $asset = Asset::create([
                    'asset_tag' => $assetTag,
                    'name' => $row['name'] ?? '',
                    'asset_category_id' => $categoryId,
                    'manufacturer' => $row['manufacturer'] ?? $row['merk'] ?? null,
                    'model' => $row['model'] ?? null,
                    'serial_number' => $row['serial_number'] ?? $row['serial'] ?? null,
                    'status' => $row['status'] ?? 'available',
                    'location' => $row['location'] ?? $row['lokasi'] ?? null,
                    'vessel_name' => $row['vessel'] ?? $row['vessel_name'] ?? null,
                    'ip_address' => $row['ip_address'] ?? $row['ip'] ?? null,
                    'mac_address' => $row['mac_address'] ?? $row['mac'] ?? null,
                    'company_id' => $company?->id,
                    'notes' => $row['notes'] ?? $row['catatan'] ?? null,
                ]);

                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'user_id' => Auth::id(),
                    'action' => 'imported',
                    'description' => 'Imported via CSV',
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$i}: " . $e->getMessage();
            }
        }

        $msg = "{$imported} asset(s) imported successfully.";
        if (!empty($errors)) {
            $msg .= ' ' . count($errors) . ' error.';
        }

        return redirect()->route('assets.index')->with('success', $msg);
    }

    public function downloadTemplate()
    {
        $headers = ['name', 'category', 'manufacturer', 'model', 'serial_number', 'status', 'location', 'vessel', 'ip_address', 'mac_address', 'notes'];
        $example = ['Laptop Dell Latitude 5420', 'Laptop', 'Dell', 'Latitude 5420', 'ABC123XYZ', 'in_use', 'Head Office', '', '192.168.1.10', '', 'Unit baru'];

        $callback = function () use ($headers, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="asset-import-template.csv"',
        ]);
    }
}
