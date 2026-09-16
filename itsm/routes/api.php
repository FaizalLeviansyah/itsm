<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentController;

// Pastikan route-nya persis sama dengan yang ada di script PowerShell kamu
Route::post('/agent/sync', [AgentController::class, 'syncData']);