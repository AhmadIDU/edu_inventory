<?php

use App\Http\Controllers\Public\AssetLabelController;
use App\Http\Controllers\Public\AssetScanController;
use Illuminate\Support\Facades\Route;

// Redirect root to super-admin panel login
Route::get('/', fn () => redirect('/super'));

// Public QR scan — no auth required
Route::get('/scan/{token}', [AssetScanController::class, 'show'])->name('asset.scan');

// QR code PNG download — no auth (or wrap in auth middleware if needed)
Route::get('/qr/{token}', [AssetScanController::class, 'downloadQr'])->name('asset.qr.download');

// Print label PDF — no auth
Route::get('/label/{token}', [AssetLabelController::class, 'show'])->name('asset.label');
