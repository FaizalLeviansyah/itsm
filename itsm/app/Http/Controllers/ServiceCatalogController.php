<?php

namespace App\Http\Controllers;

use App\Models\ServiceCatalog;
use Illuminate\Http\Request;

class ServiceCatalogController extends Controller
{
    public function index()
    {
        $services = ServiceCatalog::with('category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category.name');

        return view('services.index', compact('services'));
    }
}
