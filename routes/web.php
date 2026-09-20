<?php

use App\Livewire\Portal\DriverPortal;
use App\Livewire\Portal\GatePassTerminal;
use App\Livewire\Portal\MobileTerminal;
use App\Livewire\Portal\TripRequestPortal;
use Illuminate\Support\Facades\Route;

// Redirect root to Trip Request Portal
Route::get('/', function () {
    return redirect()->route('portal.requests');
})->name('home');

// Front-Office & Field Portals
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/requests', TripRequestPortal::class)->name('requests');
    // Backward compatibility redirect for old URL
    Route::get('/requisitions', function () {
        return redirect()->route('portal.requests');
    })->name('requisitions');
    Route::get('/gate-pass', GatePassTerminal::class)->name('gate-pass');
    Route::get('/driver', DriverPortal::class)->name('driver');
    Route::get('/mobile', MobileTerminal::class)->name('mobile');
});

// Dynamic Language Switcher Route
Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['en', 'bn'], true)) {
        session(['locale' => $lang]);
        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $lang]);
        }
    }

    return back();
})->name('locale.switch');
