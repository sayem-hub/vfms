<?php

use App\Models\Company;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('digital pre-requisition eliminates manual paper memo and transitions through admin approval and erp tagging', function () {
    $company = Company::create(['name' => 'N.A.Z. Bangladesh Ltd', 'code' => 'NAZ']);
    $adminHead = User::create([
        'name' => 'Admin Head',
        'email' => 'adminhead@example.com',
        'password' => bcrypt('secret'),
    ]);
    $transportIncharge = User::create([
        'name' => 'Transport Incharge',
        'email' => 'transport@example.com',
        'password' => bcrypt('secret'),
    ]);
    $storeOfficer = User::create([
        'name' => 'Store Officer',
        'email' => 'store@example.com',
        'password' => bcrypt('secret'),
    ]);

    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'registration_no' => 'DHAKA-METRO-CHA-11-2345',
        'current_odometer' => 45000,
    ]);

    // Step 1: Transport Incharge creates digital Pre-Requisition in VFMS (replacing paper memo)
    $record = MaintenanceRecord::create([
        'work_order_no' => 'WO-202609-TEST',
        'pre_requisition_no' => 'MPR-202609-TEST',
        'vehicle_id' => $vehicle->id,
        'transport_requester_id' => $transportIncharge->id,
        'maintenance_type' => 'ACCIDENT_BODYWORK',
        'workshop_type' => 'EXTERNAL_VENDOR_GARAGE',
        'vendor_name' => 'Bismillah Automobile Workshop',
        'odometer_at_service' => 45000,
        'service_date' => now(),
        'service_description' => 'Rear left door denting and painting',
        'estimated_cost' => 5000.00,
        'admin_approval_status' => 'PENDING_ADMIN_APPROVAL',
        'payment_status' => 'PENDING_ADMIN_APPROVAL',
        'requires_old_parts_surrender' => true,
        'is_old_parts_surrendered' => false,
    ]);

    expect($record->isApprovedByAdmin())->toBeFalse()
        ->and($record->isErpTagged())->toBeFalse();

    // Step 2: Admin Head digitally approves the pre-requisition in VFMS
    $record->update([
        'admin_head_id' => $adminHead->id,
        'admin_approval_status' => 'APPROVED_BY_ADMIN',
        'admin_approved_at' => now(),
        'admin_remarks' => 'Approved for central store ERP process.',
        'status' => 'ADMIN_APPROVED_AWAITING_ERP',
    ]);

    expect($record->fresh()->isApprovedByAdmin())->toBeTrue();

    // Step 3: Central Store generates ERP SRQ, and Transport Incharge tags it
    $record->update([
        'erp_requisition_type' => 'SERVICE_REQUISITION',
        'erp_requisition_no' => 'NAZBL-SRQ-26-00389',
        'erp_requisition_date' => now(),
        'erp_requisition_tagged_by' => $transportIncharge->id,
        'erp_requisition_tagged_at' => now(),
        'status' => 'ERP_REQ_TAGGED',
        'payment_status' => 'PENDING_PARTS_SURRENDER',
    ]);

    expect($record->fresh()->isErpTagged())->toBeTrue()
        ->and($record->fresh()->erp_requisition_no)->toBe('NAZBL-SRQ-26-00389');

    // Step 4: Outside vendor repair with Returnable Gate Pass (RGP)
    $record->update([
        'needs_vendor_repair_gatepass' => true,
        'erp_gatepass_type' => 'RETURNABLE_GATE_PASS',
        'erp_gatepass_no' => 'GP-2026-00441',
        'parts_sent_to_vendor_at' => now(),
    ]);

    expect($record->fresh()->needs_vendor_repair_gatepass)->toBeTrue()
        ->and($record->fresh()->erp_gatepass_no)->toBe('GP-2026-00441');

    // Step 5: Central Store receives old scrap parts, unlocking payment clearance
    $record->update([
        'is_old_parts_surrendered' => true,
        'store_acknowledged_by' => $storeOfficer->id,
        'store_acknowledged_at' => now(),
        'payment_status' => 'READY_FOR_PAYMENT',
        'status' => 'COMPLETED',
    ]);

    expect($record->fresh()->is_old_parts_surrendered)->toBeTrue()
        ->and($record->fresh()->payment_status)->toBe('READY_FOR_PAYMENT')
        ->and($record->fresh()->storeAcknowledgedBy->id)->toBe($storeOfficer->id);
});

test('admin maintenance records filament page renders successfully without error', function () {
    $admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'role' => 'ADMIN',
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($admin);

    $response = $this->get('/admin/maintenance-records');
    $response->assertOk();
});

test('admin maintenance records create and edit pages render successfully without error', function () {
    $admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'role' => 'ADMIN',
        'password' => bcrypt('password'),
    ]);

    $company = Company::create(['name' => 'N.A.Z. Bangladesh Ltd', 'code' => 'NAZ']);
    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'registration_no' => 'DHAKA-METRO-CHA-11-9999',
        'current_odometer' => 30000,
    ]);

    $record = MaintenanceRecord::create([
        'work_order_no' => 'WO-TEST-EDIT',
        'pre_requisition_no' => 'MPR-TEST-EDIT',
        'vehicle_id' => $vehicle->id,
        'maintenance_type' => 'SCHEDULED_PREVENTIVE',
        'workshop_type' => 'FACTORY_IN_HOUSE_WORKSHOP',
        'odometer_at_service' => 30000,
        'service_date' => now(),
        'service_description' => 'Oil and filter check',
    ]);

    $this->actingAs($admin);

    // Test Create Page
    $responseCreate = $this->get('/admin/maintenance-records/create');
    $responseCreate->assertOk();

    // Test Edit Page
    $responseEdit = $this->get("/admin/maintenance-records/{$record->id}/edit");
    $responseEdit->assertOk();
});
