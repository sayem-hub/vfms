<?php

use App\Livewire\Auth\FieldLogin;
use App\Livewire\Portal\DriverPortal;
use App\Livewire\Portal\MobileTerminal;
use App\Models\Company;
use App\Models\Driver;
use App\Models\FactoryUnit;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::create([
        'name' => 'N.A.Z. Bangladesh Ltd',
        'code' => 'NAZ',
    ]);

    $this->unit = FactoryUnit::create([
        'company_id' => $this->company->id,
        'location_code' => 'BKBARI',
        'name' => 'BK Bari Plant',
        'latitude' => 24.1036,
        'longitude' => 90.3991,
    ]);

    $this->vehicleRafiq = Vehicle::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->unit->id,
        'registration_no' => 'DHA-METRO-GA-16-0158',
        'vehicle_type' => 'MICROBUS',
        'current_odometer' => 45200,
        'fuel_type' => 'OCTANE',
    ]);

    $this->vehicleKarim = Vehicle::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->unit->id,
        'registration_no' => 'DHA-METRO-UA-14-3321',
        'vehicle_type' => 'COVERED_VAN_5T',
        'current_odometer' => 88100,
        'fuel_type' => 'DIESEL',
    ]);

    // Driver 1: Rafiq
    $this->userRafiq = User::create([
        'name' => 'Md. Rafiqul Islam',
        'email' => 'rafiq@vfms.local',
        'role' => 'DRIVER',
        'employee_id' => 'EMP-DRV-1042',
        'phone' => '01711000001',
        'password' => Hash::make('password'),
        'pin' => Hash::make('1234'),
        'is_active' => true,
    ]);

    $this->driverRafiq = Driver::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->unit->id,
        'name' => 'Md. Rafiqul Islam',
        'office_id_card' => 'EMP-DRV-1042',
        'phone' => '01711000001',
        'license_number' => 'DL-987654321',
        'license_expiry_date' => now()->addYear(),
        'current_vehicle_id' => $this->vehicleRafiq->id,
        'user_id' => $this->userRafiq->id,
        'salary' => 22000.0,
    ]);

    // Driver 2: Karim
    $this->userKarim = User::create([
        'name' => 'Abdul Karim',
        'email' => 'karim@vfms.local',
        'role' => 'DRIVER',
        'employee_id' => 'EMP-DRV-1088',
        'phone' => '01811000002',
        'password' => Hash::make('password'),
        'pin' => Hash::make('1234'),
        'is_active' => true,
    ]);

    $this->driverKarim = Driver::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->unit->id,
        'name' => 'Abdul Karim',
        'office_id_card' => 'EMP-DRV-1088',
        'phone' => '01811000002',
        'license_number' => 'DL-123456789',
        'license_expiry_date' => now()->addYear(),
        'current_vehicle_id' => $this->vehicleKarim->id,
        'user_id' => $this->userKarim->id,
        'salary' => 25000.0,
    ]);

    // Security Guard
    $this->userGuard = User::create([
        'name' => 'BK Bari Main Gate Security',
        'email' => 'guard@vfms.local',
        'role' => 'SECURITY_GUARD',
        'employee_id' => 'GUARD-GZP-01',
        'phone' => '01711999001',
        'password' => Hash::make('password'),
        'pin' => Hash::make('1234'),
        'is_active' => true,
    ]);

    // Admin
    $this->userAdmin = User::create([
        'name' => 'Fleet Admin',
        'email' => 'admin@vfms.local',
        'role' => 'ADMIN',
        'employee_id' => 'EMP-ADM-001',
        'phone' => '01711000000',
        'password' => Hash::make('password'),
        'pin' => Hash::make('1234'),
        'is_active' => true,
    ]);
});

test('driver can authenticate via mobile number and 4-digit PIN', function () {
    Livewire::test(FieldLogin::class)
        ->set('identifier', '01711000001')
        ->set('pin', '1234')
        ->call('authenticate')
        ->assertRedirect(route('portal.driver'));

    $this->assertAuthenticatedAs($this->userRafiq);
});

test('driver can authenticate via employee punch ID and 4-digit PIN', function () {
    Livewire::test(FieldLogin::class)
        ->set('identifier', 'EMP-DRV-1042')
        ->set('pin', '1234')
        ->call('authenticate')
        ->assertRedirect(route('portal.driver'));

    $this->assertAuthenticatedAs($this->userRafiq);
});

test('security guard can authenticate via badge ID and PIN and redirects to gate pass', function () {
    Livewire::test(FieldLogin::class)
        ->set('identifier', 'GUARD-GZP-01')
        ->set('pin', '1234')
        ->call('authenticate')
        ->assertRedirect(route('portal.gate-pass'));

    $this->assertAuthenticatedAs($this->userGuard);
});

test('authentication fails with invalid PIN or non-existent ID', function () {
    Livewire::test(FieldLogin::class)
        ->set('identifier', '01711000001')
        ->set('pin', '9999')
        ->call('authenticate')
        ->assertSet('errorMessage', fn ($msg) => ! empty($msg));

    $this->assertGuest();
});

test('driver cannot see or switch to another driver in driver portal', function () {
    $this->actingAs($this->userRafiq);

    Livewire::test(DriverPortal::class)
        ->assertSet('selectedDriverId', $this->driverRafiq->id)
        ->assertViewHas('isDriverUser', true)
        ->assertSee('DHA-METRO-GA-16-0158')
        ->assertDontSee('DHA-METRO-UA-14-3321');
});

test('driver is aborted 403 if attempting to select another driver ID', function () {
    $this->actingAs($this->userRafiq);

    Livewire::test(DriverPortal::class)
        ->call('selectDriver', $this->driverKarim->id)
        ->assertStatus(403);
});

test('driver cannot access security gate pass terminal', function () {
    $this->actingAs($this->userRafiq);

    $response = $this->get(route('portal.gate-pass'));
    $response->assertStatus(403);
});

test('security guard cannot access driver portal', function () {
    $this->actingAs($this->userGuard);

    $response = $this->get(route('portal.driver'));
    $response->assertStatus(403);
});

test('security guard can access gate pass terminal', function () {
    $this->actingAs($this->userGuard);

    $response = $this->get(route('portal.gate-pass'));
    $response->assertStatus(200);
});

test('navigation bar displays Trip Requests and no longer displays Requisitions', function () {
    $this->actingAs($this->userRafiq);

    $responseBn = $this->withSession(['locale' => 'bn'])->get(route('portal.driver'));
    $responseBn->assertStatus(200);
    $responseBn->assertSee('ট্রিপ রিকোয়েস্ট');
    $responseBn->assertDontSee('রিকুইজিশন');

    $responseEn = $this->withSession(['locale' => 'en'])->get(route('portal.driver'));
    $responseEn->assertStatus(200);
    $responseEn->assertSee('Requests');
    $responseEn->assertDontSee('Requisitions');
});

test('driver only sees Driver Portal and Requests in navigation, hiding Gate Pass and Terminal', function () {
    $this->actingAs($this->userRafiq);

    $response = $this->withSession(['locale' => 'bn'])->get(route('portal.driver'));
    $response->assertStatus(200);
    $response->assertSee(route('portal.driver'));
    $response->assertSee(route('portal.requests'));
    $response->assertDontSee(route('portal.gate-pass'));
    $response->assertDontSee(route('portal.mobile'));
});

test('security guard only sees Gate Pass in navigation, hiding Driver, Requests, and Terminal', function () {
    $this->actingAs($this->userGuard);

    $response = $this->withSession(['locale' => 'bn'])->get(route('portal.gate-pass'));
    $response->assertStatus(200);
    $response->assertSee(route('portal.gate-pass'));
    $response->assertDontSee(route('portal.driver'));
    $response->assertDontSee(route('portal.requests'));
    $response->assertDontSee(route('portal.mobile'));
});

test('driver accessing portal.mobile is automatically redirected to portal.driver', function () {
    $this->actingAs($this->userRafiq);

    Livewire::test(MobileTerminal::class)
        ->assertRedirect(route('portal.driver'));
});

test('security guard accessing portal.mobile is automatically redirected to portal.gate-pass', function () {
    $this->actingAs($this->userGuard);

    Livewire::test(MobileTerminal::class)
        ->assertRedirect(route('portal.gate-pass'));
});
