<?php

use Botble\Theme\Facades\Theme;
use Botble\ToyyibPay\Http\Controllers\ToyyibPayController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::prefix('payment/toyyibpay')
        ->name('payments.toyyibpay.')
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->group(function (): void {
            Route::post('callback', [ToyyibPayController::class, 'callback'])->name('callback');
            Route::match(['get', 'post'], 'return', [ToyyibPayController::class, 'return'])->name('return');
        });
}, ['core']);
