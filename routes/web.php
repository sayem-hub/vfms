<?php

use App\Livewire\Portal\DriverPortal;
use App\Livewire\Portal\GatePassTerminal;
use App\Livewire\Portal\TripRequisitionPortal;
use Illuminate\Support\Facades\Route;

// Redirect root to Trip Requisition Portal
Route::get('/', function () {
    return redirect()->route('portal.requisitions');
})->name('home');

// Front-Office & Field Portals
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/requisitions', TripRequisitionPortal::class)->name('requisitions');
    Route::get('/gate-pass', GatePassTerminal::class)->name('gate-pass');
    Route::get('/driver', DriverPortal::class)->name('driver');
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
