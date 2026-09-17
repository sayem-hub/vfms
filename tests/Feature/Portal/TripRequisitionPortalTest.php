<?php

use App\Livewire\Portal\TripRequisitionPortal;
use App\Models\Company;
use App\Models\FactoryUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('trip requisition portal renders successfully in both english and bengali', function () {
    $company = Company::create(['name' => 'Apex Knit', 'code' => 'AKC']);
    $unit = FactoryUnit::create(['company_id' => $company->id, 'name' => 'Unit 1', 'location_code' => 'GZP-01']);

    app()->setLocale('bn');
    $responseBn = $this->get('/portal/requisitions');
    $responseBn->assertOk();
    $responseBn->assertSee('গাড়ি ও ট্রিপ রিকুইজিশন');

    app()->setLocale('en');
    $responseEn = $this->get('/portal/requisitions');
    $responseEn->assertOk();
    $responseEn->assertSee('Vehicle & Trip Requisition Portal');
});

test('user can submit a new trip requisition through the portal', function () {
    $company = Company::create(['name' => 'Apex Knit', 'code' => 'AKC']);
    $unit = FactoryUnit::create(['company_id' => $company->id, 'name' => 'Unit 1', 'location_code' => 'GZP-01']);
    $user = User::create(['name' => 'Test Staff', 'email' => 'staff@example.com', 'password' => bcrypt('password')]);

    $this->actingAs($user);

    Livewire::test(TripRequisitionPortal::class)
        ->set('company_id', $company->id)
        ->set('factory_unit_id', $unit->id)
        ->set('trip_type', 'OFFICIAL_DUTY')
        ->set('purpose', 'Audit & Inspection at Mawna')
        ->set('origin_name', 'Gulshan HO')
        ->set('destination_name', 'Mawna Factory')
        ->set('origin_latitude', 23.7925)
        ->set('origin_longitude', 90.4078)
        ->set('destination_latitude', 24.1850)
        ->set('destination_longitude', 90.4320)
        ->set('scheduled_start_time', now()->addHours(2)->format('Y-m-d\TH:i'))
        ->set('erp_requisition_no', 'ERP-REQ-9988')
        ->call('submitRequisition')
        ->assertSet('showSuccessModal', true);

    $this->assertDatabaseHas('trip_requisitions', [
        'company_id' => $company->id,
        'erp_requisition_no' => 'ERP-REQ-9988',
        'status' => 'SUBMITTED',
    ]);
});
