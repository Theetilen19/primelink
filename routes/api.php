<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\WhatsAppController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Payment Gateway Webhooks
Route::prefix('webhooks')->group(function () {
    Route::post('/midtrans', [PaymentWebhookController::class, 'midtrans'])->name('webhook.midtrans');
    Route::post('/xendit', [PaymentWebhookController::class, 'xendit'])->name('webhook.xendit');
});

// WhatsApp API
Route::prefix('whatsapp')->group(function () {
    Route::post('/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');
    Route::get('/status', [WhatsAppController::class, 'status'])->name('whatsapp.status');
});

// Payment Routes (Public)
Route::prefix('payment')->group(function () {
    Route::get('/finish', function (Request $request) {
        return redirect()->route('payment.success', ['order_id' => $request->order_id]);
    })->name('payment.finish');
    
    Route::get('/success', function () {
        return view('payment.success');
    })->name('payment.success');
    
    Route::get('/failed', function () {
        return view('payment.failed');
    })->name('payment.failed');
});

// Authenticated API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
