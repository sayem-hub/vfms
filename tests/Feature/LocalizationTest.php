<?php

use Illuminate\Support\Facades\App;

test('default locale is en and translation works', function () {
    App::setLocale('en');
    expect(__('vfms.driver'))->toBe('Driver')
        ->and(__('vfms.requisition_no'))->toBe('Requisition No');
});

test('bengali locale translations work correctly', function () {
    App::setLocale('bn');
    expect(__('vfms.driver'))->toBe('ড্রাইভার')
        ->and(__('vfms.fuel_refill'))->toBe('জ্বালানী / গ্যাস রিফিল')
        ->and(__('vfms.distance_anomaly'))->toBe('অস্বাভাবিক দূরত্বের সতর্কতা (>১৫%)');
});

test('locale toggle route switches session locale', function () {
    $response = $this->get('/locale/bn');
    $response->assertSessionHas('locale', 'bn');

    $response2 = $this->get('/locale/en');
    $response2->assertSessionHas('locale', 'en');
});
