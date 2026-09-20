<?php

use App\Livewire\Auth\FieldLogin;
use App\Livewire\Portal\DriverPortal;
use App\Livewire\Portal\GatePassTerminal;
use App\Livewire\Portal\MobileTerminal;
use App\Livewire\Portal\TripRequestPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to Trip Request Portal
Route::get('/', function () {
    return redirect()->route('portal.requests');
})->name('home');

// Field Authentication Routes
Route::get('/login', FieldLogin::class)->name('login');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
});

// Front-Office & Field Portals
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/requests', TripRequestPortal::class)->name('requests');

    // Backward compatibility redirect for old URL
    Route::get('/requisitions', function () {
        return redirect()->route('portal.requests');
    })->name('requisitions');

    // Protected Field Portals with Role-Based Access Control
    Route::get('/gate-pass', GatePassTerminal::class)
        ->middleware(['auth', 'role:SECURITY_GUARD,ADMIN,TRANSPORT_OFFICER'])
        ->name('gate-pass');

    Route::get('/driver', DriverPortal::class)
        ->middleware(['auth', 'role:DRIVER,ADMIN,TRANSPORT_OFFICER'])
        ->name('driver');

    Route::get('/mobile', MobileTerminal::class)
        ->middleware(['auth'])
        ->name('mobile');
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
