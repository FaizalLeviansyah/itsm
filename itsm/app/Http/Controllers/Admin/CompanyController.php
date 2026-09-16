<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class CompanyController extends Controller
{
    public function index()
    {
        // Membaca dari database agar logo bisa tampil,
        // TAPI isinya adalah hasil sinkronisasi dari API Pusat.
        $companies = Company::withCount(['users', 'assets', 'tickets'])->get();
        return view('admin.settings.companies', compact('companies'));
    }

    public function syncApi()
    {
        try {
            // Menggunakan struktur endpoint universal master API pusat
            $response = Http::withHeaders([
                'X-API-KEY' => env('MASTER_API_KEY', 'AZX09KWEGRKT8hiwBHoliBOOI6zbRg5X'),
                'Accept' => 'application/json',
            ])->timeout(10)->get('http://api.amarin.biz.id/api/v1/data/db_master_amarin_original/tbl_company');

            if ($response->failed()) {
                throw new \Exception('Gagal terhubung ke server API Pusat. Status: ' . $response->status());
            }

            $result = $response->json();
            $apiData = isset($result['data']) ? $result['data'] : $result;

            if (!is_array($apiData)) {
                throw new \Exception('Format data dari API Pusat tidak valid.');
            }

            foreach ($apiData as $data) {
                // Sesuaikan key array dengan kolom asli di tabel master pusat jika berbeda
                $companyCode = $data['code'] ?? $data['company_code'] ?? null;
                if (!$companyCode) continue;

                Company::updateOrCreate(
                    ['code' => $companyCode], 
                    [
                        'id'          => $data['id'] ?? null,
                        'name'        => $data['name'] ?? $data['company_name'],
                        'full_name'   => $data['full_name'] ?? $data['name'] ?? $data['company_name'],
                        'asset_prefix'=> $data['asset_prefix'] ?? $companyCode
                    ]
                );
            }

            return redirect()->back()->with('success', 'Berhasil! Data perusahaan telah disinkronkan langsung dari API Pusat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan sinkronisasi: ' . $e->getMessage());
        }
    }

    public function updateLogo(Request $request, Company $company)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            
            // Simpan logo baru
            $company->logo = $request->file('logo')->store('companies', 'public');
            $company->save();
        }

        return redirect()->back()->with('success', 'Logo perusahaan berhasil diperbarui!');
    }
}