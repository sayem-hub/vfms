<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['en', 'bn'], true)) {
        session(['locale' => $lang]);
        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $lang]);
        }
    }

    return back();
})->name('locale.switch');
