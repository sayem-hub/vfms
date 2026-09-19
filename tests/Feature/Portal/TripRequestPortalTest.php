<?php

use App\Livewire\Portal\TripRequestPortal;
use App\Models\Company;
use App\Models\FactoryUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('trip request portal renders successfully in both english and bengali', function () {
    $company = Company::create(['name' => 'N.A.Z. Bangladesh Ltd', 'code' => 'NAZ']);
    $unit = FactoryUnit::create(['company_id' => $company->id, 'name' => 'Unit 1', 'location_code' => 'GZP-01']);

    app()->setLocale('bn');
    $responseBn = $this->get('/portal/requests');
    $responseBn->assertOk();
    $responseBn->assertSee('গাড়ি ও ট্রিপ রিকোয়েস্ট');

    app()->setLocale('en');
    $responseEn = $this->get('/portal/requests');
    $responseEn->assertOk();
    $responseEn->assertSee('Vehicle & Trip Request Portal');
});

test('user can submit a new trip request through the portal', function () {
    $company = Company::create(['name' => 'N.A.Z. Bangladesh Ltd', 'code' => 'NAZ']);
    $unit = FactoryUnit::create(['company_id' => $company->id, 'name' => 'Unit 1', 'location_code' => 'GZP-01']);
    $user = User::create(['name' => 'Test Staff', 'email' => 'staff@example.com', 'password' => bcrypt('password')]);

    $this->actingAs($user);

    Livewire::test(TripRequestPortal::class)
        ->set('company_id', $company->id)
        ->set('factory_unit_id', $unit->id)
        ->set('trip_type', 'OFFICIAL_DUTY')
        ->set('purpose', 'Audit & Inspection at Mawna')
        ->set('origin_name', 'Baridhara HO')
        ->set('destination_name', 'BK Bari Factory')
        ->set('origin_latitude', 23.8050)
        ->set('origin_longitude', 90.4180)
        ->set('destination_latitude', 24.0400)
        ->set('destination_longitude', 90.3950)
        ->set('scheduled_start_time', now()->addHours(2)->format('Y-m-d\TH:i'))
        ->call('submitRequest')
        ->assertSet('showSuccessModal', true);

    $this->assertDatabaseHas('trip_requests', [
        'company_id' => $company->id,
        'origin_name' => 'Baridhara HO',
        'destination_name' => 'BK Bari Factory',
        'status' => 'SUBMITTED',
    ]);
});
