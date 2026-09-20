<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CostAllocation;
use App\Models\Driver;
use App\Models\FactoryUnit;
use App\Models\FixedRoute;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCompliance;
use App\Models\VehicleGateLog;
use App\Services\Financial\CostAllocationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Key Role Users (Admin, Admin Head, Transport Incharge, Store Officer, Cashier)
        $admin = User::firstOrCreate(
            ['email' => 'admin@vfms.com'],
            [
                'name' => 'Transport General Manager',
                'password' => Hash::make('password'),
                'preferred_locale' => 'bn',
            ]
        );

        $adminHead = User::firstOrCreate(
            ['email' => 'adminhead@vfms.com'],
            [
                'name' => 'Head of Administration (NZ Group)',
                'password' => Hash::make('password'),
                'preferred_locale' => 'bn',
            ]
        );

        $transportIncharge = User::firstOrCreate(
            ['email' => 'transport@vfms.com'],
            [
                'name' => 'Transport Incharge (BK Bari Plant)',
                'password' => Hash::make('password'),
                'preferred_locale' => 'bn',
            ]
        );

        $storeOfficer = User::firstOrCreate(
            ['email' => 'store@vfms.com'],
            [
                'name' => 'Central Store Officer',
                'password' => Hash::make('password'),
                'preferred_locale' => 'bn',
            ]
        );

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@vfms.com'],
            [
                'name' => 'Factory Central Cashier',
                'password' => Hash::make('password'),
                'preferred_locale' => 'bn',
            ]
        );

        // 2. Create Companies & Units
        $apex = Company::firstOrCreate(
            ['code' => 'NAZ'],
            [
                'name' => 'N.A.Z. Bangladesh Ltd',
                'address' => 'BK Bari, Gazipur',
                'phone' => '+8801711001122',
                'email' => 'info@nz-bd.com',
            ]
        );

        $cakl = Company::firstOrCreate(
            ['code' => 'CAKL'],
            [
                'name' => 'CA Knitwear Ltd',
                'address' => 'Bhobanipur, Gazipur',
                'phone' => '+8801711334455',
                'email' => 'info@nz-bd.com',
            ]
        );

        $unitGazipur = FactoryUnit::firstOrCreate(
            ['location_code' => 'BKBARI'],
            [
                'company_id' => $apex->id,
                'name' => 'Garments and Textile Complex',
                'latitude' => 24.0400,
                'longitude' => 90.3950,
                'address' => 'BK Bari, Gazipur',
            ]
        );

        $unitBhobanipur = FactoryUnit::firstOrCreate(
            ['location_code' => 'BHBNPR'],
            [
                'company_id' => $cakl->id,
                'name' => 'Garments Unit 2',
                'latitude' => 24.1150,
                'longitude' => 90.4120,
                'address' => 'Bhobanipur, Gazipur',
            ]
        );

        // 3. Create Vehicles
        $microbus = Vehicle::firstOrCreate(
            ['registration_no' => 'DHAKA METRO-CHA-11-2345'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'vehicle_type' => 'MICROBUS',
                'ownership_type' => 'COMPANY_OWNED',
                'fuel_type' => 'OCTANE',
                'fuel_capacity_liters' => 65.0,
                'expected_km_per_liter' => 8.5,
                'current_odometer' => 45200,
                'brand' => 'Toyota',
                'model_name' => 'HiAce GL',
                'model_year' => '2023',
                'status' => 'AVAILABLE',
            ]
        );

        $coveredVan = Vehicle::firstOrCreate(
            ['registration_no' => 'DHAKA METRO-TA-14-8899'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'vehicle_type' => 'COVERED_VAN_5T',
                'ownership_type' => 'RENTED_VENDOR',
                'fuel_type' => 'DIESEL',
                'fuel_capacity_liters' => 120.0,
                'expected_km_per_liter' => 4.2,
                'current_odometer' => 82100,
                'rate_per_km' => 45.0,
                'brand' => 'Isuzu',
                'model_name' => 'Forward 5-Ton',
                'model_year' => '2022',
                'status' => 'AVAILABLE',
            ]
        );

        $ambulance = Vehicle::firstOrCreate(
            ['registration_no' => 'DHAKA METRO-CHA-51-9001'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'vehicle_type' => 'AMBULANCE',
                'ownership_type' => 'COMPANY_OWNED',
                'fuel_type' => 'OCTANE',
                'fuel_capacity_liters' => 50.0,
                'expected_km_per_liter' => 9.0,
                'current_odometer' => 18500,
                'brand' => 'Toyota',
                'model_name' => 'Noah Ambulance Spec',
                'model_year' => '2024',
                'status' => 'AVAILABLE',
            ]
        );

        // 4. Create Drivers (With office_id_card, photo, factory_unit_id)
        $driverRafiq = Driver::firstOrCreate(
            ['license_number' => 'DL-987654321'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'name' => 'Md. Rafiqul Islam',
                'office_id_card' => 'EMP-DRV-1042',
                'phone' => '01711000001',
                'nid_number' => '19852691234567890',
                'license_type' => 'MEDIUM',
                'license_expiry_date' => now()->addMonths(8),
                'employment_type' => 'COMPANY_PAYROLL',
                'salary' => 22000.0,
                'current_vehicle_id' => $microbus->id,
                'preferred_locale' => 'bn',
            ]
        );

        $driverKarim = Driver::firstOrCreate(
            ['license_number' => 'DL-123456789'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'name' => 'Abdul Karim',
                'office_id_card' => 'EMP-DRV-1088',
                'phone' => '01811000002',
                'license_type' => 'HEAVY',
                'license_expiry_date' => now()->addMonths(14),
                'employment_type' => 'VENDOR_DRIVER',
                'current_vehicle_id' => $coveredVan->id,
                'preferred_locale' => 'bn',
            ]
        );

        // 5. Create BRTA Compliances (One expiring in 12 days to test radar alert!)
        VehicleCompliance::firstOrCreate(
            ['certificate_number' => 'BRTA-FIT-2026-GZP01'],
            [
                'vehicle_id' => $microbus->id,
                'document_type' => 'FITNESS_CERTIFICATE',
                'issue_date' => now()->subYear()->addDays(12),
                'expiry_date' => now()->addDays(12), // Expiring in 12 days!
                'renewal_cost' => 4500.0,
            ]
        );

        VehicleCompliance::firstOrCreate(
            ['certificate_number' => 'BRTA-TAX-2026-GZP02'],
            [
                'vehicle_id' => $microbus->id,
                'document_type' => 'TAX_TOKEN',
                'issue_date' => now()->subMonths(6),
                'expiry_date' => now()->addMonths(6),
                'renewal_cost' => 6000.0,
            ]
        );

        // 6. Create Sample Trip Request
        $trip = TripRequest::firstOrCreate(
            ['request_no' => 'TR-202609-001'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'requester_id' => $admin->id,
                'vehicle_id' => $microbus->id,
                'driver_id' => $driverRafiq->id,
                'trip_type' => 'OFFICIAL_DUTY',
                'purpose' => 'Management Audit & Buyer Compliance Visit at BK Bari Unit',
                'origin_name' => 'Corporate Head Office, Baridhara DOHS, Dhaka',
                'destination_name' => 'BK Bari Plant, NAZ Bangladesh Ltd, Gazipur',
                'origin_latitude' => 23.8050,
                'origin_longitude' => 90.4180,
                'destination_latitude' => 24.0400,
                'destination_longitude' => 90.3950,
                'scheduled_start_time' => now()->subHours(8),
                'scheduled_end_time' => now()->subHours(2),
                'actual_start_time' => now()->subHours(8),
                'actual_end_time' => now()->subHours(2),
                'start_odometer' => 45130,
                'end_odometer' => 45200,
                'claimed_distance_km' => 70.0,
                'expected_distance_km' => 64.5,
                'distance_variance_percentage' => 8.53,
                'is_distance_anomaly' => false,
                'status' => 'COMPLETED',
            ]
        );

        // 7. Create Fuel Refill & Trip Expense Settlement
        FuelLog::firstOrCreate(
            ['odometer_reading' => 45200],
            [
                'trip_request_id' => $trip->id,
                'vehicle_id' => $microbus->id,
                'driver_id' => $driverRafiq->id,
                'fuel_type' => 'OCTANE',
                'refill_date' => now()->subHours(3),
                'station_name' => 'Trust Filling Station, Joydebpur Road',
                'fuel_quantity' => 30.0,
                'unit_price' => 125.0,
                'total_cost' => 3750.0,
                'payment_method' => 'PETTY_CASH_ADVANCE',
                'km_since_last_refill' => 255,
                'calculated_km_per_liter' => 8.50,
                'is_efficiency_anomaly' => false,
            ]
        );

        TripExpenseSettlement::firstOrCreate(
            ['trip_request_id' => $trip->id],
            [
                'driver_id' => $driverRafiq->id,
                'advance_cash_received' => 6000.0,
                'advance_received_from_user_id' => $cashier->id,
                'advance_disbursed_at' => now()->subHours(9),
                'total_fuel_expense' => 3750.0,
                'total_toll_expense' => 450.0,
                'total_parking_expense' => 100.0,
                'total_driver_food_allowance' => 300.0,
                'total_actual_expense' => 4600.0,
                'balance_amount' => 1400.0, // Driver refunds BDT 1400 to cashier
                'status' => 'AUDITED_BY_TRANSPORT',
            ]
        );

        // 8. Create Maintenance Records with Pre-Requisition, Admin Digital Approval & ERP Tagging
        // Record 1: Service Requisition (SRQ) - Body Denting/Painting approved & tagged
        MaintenanceRecord::firstOrCreate(
            ['pre_requisition_no' => 'MPR-202609-001'],
            [
                'work_order_no' => 'WO-202609-001',
                'vehicle_id' => $microbus->id,
                'maintenance_type' => 'ACCIDENT_BODYWORK',
                'workshop_type' => 'EXTERNAL_VENDOR_GARAGE',
                'vendor_name' => 'Bismillah Automobile Workshop, Gazipur',
                'vendor_phone' => '01712334455',
                'vendor_address' => 'Board Bazar, Gazipur',
                'odometer_at_service' => 45000,
                'service_date' => now()->subDays(5),
                'service_description' => 'Rear left door denting, putty, surface prep, and 2K paint coat touch-up.',
                'parts_total_cost' => 1200.00,
                'labor_total_cost' => 3800.00,
                'grand_total_cost' => 5000.00,
                'transport_requester_id' => $transportIncharge->id,
                'estimated_cost' => 5500.00,
                'admin_head_id' => $adminHead->id,
                'admin_approval_status' => 'APPROVED_BY_ADMIN',
                'admin_approved_at' => now()->subDays(6),
                'admin_remarks' => 'Approved as per site inspection report. Send to store for ERP SRQ generation.',
                'erp_requisition_type' => 'SERVICE_REQUISITION',
                'erp_requisition_no' => 'NAZBL-SRQ-26-00389',
                'erp_requisition_date' => now()->subDays(5),
                'erp_requisition_tagged_by' => $transportIncharge->id,
                'erp_requisition_tagged_at' => now()->subDays(5),
                'requires_old_parts_surrender' => false,
                'is_old_parts_surrendered' => false,
                'payment_status' => 'READY_FOR_PAYMENT',
                'status' => 'ERP_REQ_TAGGED',
            ]
        );

        // Record 2: Parts Requisition (RQSN) - Mobil, Filter & Brake replacement with Scrap Surrender
        MaintenanceRecord::firstOrCreate(
            ['pre_requisition_no' => 'MPR-202609-002'],
            [
                'work_order_no' => 'WO-202609-002',
                'vehicle_id' => $coveredVan->id,
                'maintenance_type' => 'SCHEDULED_PREVENTIVE',
                'workshop_type' => 'FACTORY_IN_HOUSE_WORKSHOP',
                'odometer_at_service' => 82000,
                'service_date' => now()->subDays(2),
                'service_description' => 'Engine oil Mobil Delvac 15W40 replacement, genuine Isuzu oil filter, air filter, fuel filter, and front brake shoe replacement.',
                'parts_total_cost' => 14500.00,
                'labor_total_cost' => 1500.00,
                'grand_total_cost' => 16000.00,
                'transport_requester_id' => $transportIncharge->id,
                'estimated_cost' => 16500.00,
                'admin_head_id' => $adminHead->id,
                'admin_approval_status' => 'APPROVED_BY_ADMIN',
                'admin_approved_at' => now()->subDays(3),
                'admin_remarks' => 'Routine 10,000 KM service approved. Old brake shoes and filters must be surrendered to Central Store.',
                'erp_requisition_type' => 'PARTS_REQUISITION',
                'erp_requisition_no' => 'NAZBL-RQSN-26-02116',
                'erp_requisition_date' => now()->subDays(2),
                'erp_requisition_tagged_by' => $transportIncharge->id,
                'erp_requisition_tagged_at' => now()->subDays(2),
                'requires_old_parts_surrender' => true,
                'is_old_parts_surrendered' => true,
                'store_acknowledged_by' => $storeOfficer->id,
                'store_acknowledged_at' => now()->subDay(),
                'payment_status' => 'READY_FOR_PAYMENT',
                'status' => 'COMPLETED',
            ]
        );

        // Record 3: External Lathe Repair with Returnable Gate Pass (RGP)
        MaintenanceRecord::firstOrCreate(
            ['pre_requisition_no' => 'MPR-202609-003'],
            [
                'work_order_no' => 'WO-202609-003',
                'vehicle_id' => $coveredVan->id,
                'maintenance_type' => 'EMERGENCY_BREAKDOWN',
                'workshop_type' => 'EXTERNAL_VENDOR_GARAGE',
                'vendor_name' => 'Master Lathe Engineering Works, Joydebpur',
                'vendor_phone' => '01819998877',
                'odometer_at_service' => 82100,
                'service_date' => now(),
                'service_description' => 'Propeller shaft universal joint balancing and lathe bushing reshaping.',
                'parts_total_cost' => 4500.00,
                'labor_total_cost' => 2000.00,
                'grand_total_cost' => 6500.00,
                'transport_requester_id' => $transportIncharge->id,
                'estimated_cost' => 7000.00,
                'admin_head_id' => $adminHead->id,
                'admin_approval_status' => 'APPROVED_BY_ADMIN',
                'admin_approved_at' => now()->subHours(6),
                'admin_remarks' => 'Approved for outside vendor lathe work under Returnable Gate Pass.',
                'erp_requisition_type' => 'SERVICE_REQUISITION',
                'erp_requisition_no' => 'NAZBL-SRQ-26-00395',
                'erp_requisition_date' => now(),
                'erp_requisition_tagged_by' => $transportIncharge->id,
                'erp_requisition_tagged_at' => now()->subHours(4),
                'needs_vendor_repair_gatepass' => true,
                'erp_gatepass_type' => 'RETURNABLE_GATE_PASS',
                'erp_gatepass_no' => 'GP-2026-00441',
                'parts_sent_to_vendor_at' => now()->subHours(3),
                'requires_old_parts_surrender' => true,
                'is_old_parts_surrendered' => false,
                'payment_status' => 'PENDING_PARTS_SURRENDER',
                'status' => 'PARTS_SENT_TO_VENDOR',
            ]
        );

        // 8. Fixed & Dedicated Movement Vehicles (Management Cars & Commute Buses)
        // MD Dedicated Car
        $mdCar = Vehicle::firstOrCreate(
            ['registration_no' => 'DHAKA METRO-GA-21-9988'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'vehicle_type' => 'SEDAN_CAR',
                'usage_category' => 'DEDICATED_MANAGEMENT',
                'dedicated_to_official' => 'Managing Director (Engr. Zahirul Islam)',
                'ownership_type' => 'COMPANY_OWNED',
                'fuel_type' => 'OCTANE',
                'fuel_payer' => 'MONTHLY_QUOTA',
                'monthly_fuel_quota_liters' => 250.0,
                'fuel_capacity_liters' => 60.0,
                'expected_km_per_liter' => 10.5,
                'current_odometer' => 32100,
                'brand' => 'Toyota',
                'model_name' => 'Camry Hybrid',
                'model_year' => '2023',
                'status' => 'AVAILABLE',
            ]
        );

        $driverKabir = Driver::firstOrCreate(
            ['license_number' => 'DL-554433221'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'name' => 'Kabir Hossain',
                'office_id_card' => 'EMP-DRV-1011',
                'phone' => '01711223344',
                'license_type' => 'LIGHT',
                'license_expiry_date' => now()->addMonths(18),
                'employment_type' => 'COMPANY_PAYROLL',
                'salary' => 25000.0,
                'current_vehicle_id' => $mdCar->id,
                'preferred_locale' => 'bn',
            ]
        );

        // Director SCM Dedicated Microbus/Car
        $directorCar = Vehicle::firstOrCreate(
            ['registration_no' => 'DHAKA METRO-CHA-53-4412'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'vehicle_type' => 'MICROBUS',
                'usage_category' => 'DEDICATED_MANAGEMENT',
                'dedicated_to_official' => 'Director SCM (Mr. Ashraful Alam)',
                'ownership_type' => 'COMPANY_OWNED',
                'fuel_type' => 'OCTANE',
                'fuel_payer' => 'MONTHLY_QUOTA',
                'monthly_fuel_quota_liters' => 200.0,
                'fuel_capacity_liters' => 65.0,
                'expected_km_per_liter' => 8.5,
                'current_odometer' => 64800,
                'brand' => 'Toyota',
                'model_name' => 'Noah Super GL',
                'model_year' => '2022',
                'status' => 'AVAILABLE',
            ]
        );

        $driverShamim = Driver::firstOrCreate(
            ['license_number' => 'DL-998877665'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'name' => 'Shamim Reza',
                'office_id_card' => 'EMP-DRV-1025',
                'phone' => '01811556677',
                'license_type' => 'MEDIUM',
                'license_expiry_date' => now()->addMonths(12),
                'employment_type' => 'COMPANY_PAYROLL',
                'salary' => 23000.0,
                'current_vehicle_id' => $directorCar->id,
                'preferred_locale' => 'bn',
            ]
        );

        // Staff Commute Bus
        $staffBus = Vehicle::firstOrCreate(
            ['registration_no' => 'DHAKA METRO-BA-11-2345'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'vehicle_type' => 'STAFF_BUS',
                'usage_category' => 'STAFF_COMMUTE_BUS',
                'ownership_type' => 'COMPANY_OWNED',
                'fuel_type' => 'DIESEL',
                'fuel_payer' => 'COMPANY',
                'fuel_capacity_liters' => 150.0,
                'expected_km_per_liter' => 3.8,
                'current_odometer' => 142300,
                'brand' => 'Hino',
                'model_name' => 'AK1J Staff Bus (52 Seater)',
                'model_year' => '2021',
                'status' => 'AVAILABLE',
            ]
        );

        $driverZahid = Driver::firstOrCreate(
            ['license_number' => 'DL-332211445'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'name' => 'Md. Zahid Hasan',
                'office_id_card' => 'EMP-DRV-1055',
                'phone' => '01911889900',
                'license_type' => 'HEAVY',
                'license_expiry_date' => now()->addMonths(24),
                'employment_type' => 'COMPANY_PAYROLL',
                'salary' => 26000.0,
                'current_vehicle_id' => $staffBus->id,
                'preferred_locale' => 'bn',
            ]
        );

        // 9. Create Fixed Routes
        $routeJoydebpur = FixedRoute::firstOrCreate(
            ['route_code' => 'R-01'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'route_name' => 'Joydebpur Chowrasta ➔ BK Bari Plant Staff Commute',
                'origin_name' => 'Joydebpur Chowrasta, Gazipur',
                'destination_name' => 'NZ Group BK Bari Industrial Park',
                'standard_distance_km' => 38.5,
                'scheduled_departure_time' => '06:30 AM',
                'scheduled_return_time' => '06:30 PM',
                'shift_name' => 'General Shift',
                'assigned_vehicle_id' => $staffBus->id,
                'assigned_driver_id' => $driverZahid->id,
                'stoppages' => 'Joydebpur Chowrasta -> Shibbari -> Salna -> Rajendrapur -> Hotapara -> BK Bari Plant',
                'is_active' => true,
            ]
        );

        $routeUttara = FixedRoute::firstOrCreate(
            ['route_code' => 'R-02'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'route_name' => 'Uttara Sector 7 ➔ BK Bari Plant Management Shuttle',
                'origin_name' => 'Uttara Sector 7 (Rabindra Sarani)',
                'destination_name' => 'NZ Group BK Bari Industrial Park',
                'standard_distance_km' => 46.0,
                'scheduled_departure_time' => '06:45 AM',
                'scheduled_return_time' => '06:45 PM',
                'shift_name' => 'Executive Shift',
                'assigned_vehicle_id' => $directorCar->id,
                'assigned_driver_id' => $driverShamim->id,
                'stoppages' => 'Uttara Sec 7 -> House Building -> Tongi Station Road -> Board Bazar -> Gazipur Bypass -> BK Bari',
                'is_active' => true,
            ]
        );

        // 10. Sample Today's Vehicle Gate Logs
        // Staff bus completed morning pickup run
        VehicleGateLog::firstOrCreate(
            [
                'vehicle_id' => $staffBus->id,
                'log_date' => now()->toDateString(),
                'log_type' => 'STAFF_COMMUTE_BUS',
            ],
            [
                'driver_id' => $driverZahid->id,
                'fixed_route_id' => $routeJoydebpur->id,
                'gate_out_time' => now()->setTime(6, 30),
                'gate_in_time' => now()->setTime(7, 45),
                'out_odometer' => 142260,
                'in_odometer' => 142300,
                'total_km' => 40.0,
                'official_name' => 'Morning General Shift Staff (50 Persons)',
                'destination' => 'BK Bari Plant',
                'purpose' => 'Morning Staff Pickup',
                'status' => 'COMPLETED',
                'security_guard_id' => $admin->id,
            ]
        );

        // MD car out for Head Office visit
        VehicleGateLog::firstOrCreate(
            [
                'vehicle_id' => $mdCar->id,
                'log_date' => now()->toDateString(),
                'gate_out_time' => now()->setTime(8, 30),
            ],
            [
                'driver_id' => $driverKabir->id,
                'log_type' => 'DEDICATED_MANAGEMENT_CAR',
                'out_odometer' => 32050,
                'official_name' => 'Managing Director (Engr. Zahirul Islam)',
                'destination' => 'Baridhara DOHS Head Office',
                'purpose' => 'Management Board Meeting & HO Visit',
                'status' => 'OUT',
                'security_guard_id' => $admin->id,
            ]
        );

        // 11. Sample Monthly Quota Fuel Refill for MD Car
        FuelLog::firstOrCreate(
            [
                'vehicle_id' => $mdCar->id,
                'odometer_reading' => 32050,
            ],
            [
                'driver_id' => $driverKabir->id,
                'trip_request_id' => null, // No trip request needed for quota car
                'fuel_type' => 'OCTANE',
                'refill_date' => now()->subDay(),
                'station_name' => 'Trust Filling Station (Gulshan-2)',
                'fuel_quantity' => 60.00,
                'unit_price' => 125.00,
                'total_cost' => 7500.00,
                'calculated_km_per_liter' => 11.2,
                'is_efficiency_anomaly' => false,
                'payment_method' => 'COMPANY_CREDIT_VOUCHER',
            ]
        );

        // 12. Real Factory Fleet Vehicles & Drivers from Transport Concern's Handwritten Sheet
        $realFleet = [
            [
                'reg' => 'DHAKA METRO-GA-16-0158',
                'type' => 'SEDAN_CAR',
                'brand' => 'Toyota',
                'model' => 'Allion A15',
                'official' => 'Executive Director (ED Sir)',
                'driver_name' => 'Md. Robiul',
                'driver_phone' => '01917577119',
                'driver_id_card' => 'EMP-DRV-1012',
                'license' => 'DL-160158-99',
                'quota' => 250.0,
                'odo' => 74200,
                'purchase_price' => 3600000.00,
                'purchase_date' => '2022-03-10',
                'company' => $apex,
            ],
            [
                'reg' => 'DHAKA METRO-GA-27-6387',
                'type' => 'SEDAN_CAR',
                'brand' => 'Toyota',
                'model' => 'Corolla Altis',
                'official' => 'Md. Harun Sir',
                'driver_name' => 'Md. Romjan',
                'driver_phone' => '01643133909',
                'driver_id_card' => 'EMP-DRV-1013',
                'license' => 'DL-276387-88',
                'quota' => 200.0,
                'odo' => 58900,
                'purchase_price' => 2500000.00,
                'purchase_date' => '2023-05-14',
                'company' => $apex,
            ],
            [
                'reg' => 'DHAKA METRO-GA-20-2755',
                'type' => 'MICROBUS',
                'brand' => 'Toyota',
                'model' => 'HiAce GL (Buyer Escort)',
                'official' => 'Next Buyer Dedicated (CA Knitwear)',
                'driver_name' => 'Md. Mushfiq',
                'driver_phone' => '01856158750',
                'driver_id_card' => 'EMP-DRV-1014',
                'license' => 'DL-202755-77',
                'quota' => 220.0,
                'odo' => 86400,
                'purchase_price' => 3400000.00,
                'purchase_date' => '2022-07-20',
                'company' => $cakl,
            ],
            [
                'reg' => 'DHAKA METRO-CHA-75-0175',
                'type' => 'AMBULANCE',
                'brand' => 'Toyota',
                'model' => 'HiAce Medical Spec',
                'official' => 'Factory Medical Unit (Ambulance)',
                'driver_name' => 'Md. Raju',
                'driver_phone' => '01930898182',
                'driver_id_card' => 'EMP-DRV-1015',
                'license' => 'DL-750175-66',
                'quota' => null,
                'odo' => 21300,
                'purchase_price' => 2700000.00,
                'purchase_date' => '2024-01-15',
                'company' => $apex,
            ],
            [
                'reg' => 'DHAKA METRO-JA-11-0382',
                'type' => 'STAFF_BUS',
                'brand' => 'Isuzu',
                'model' => 'Minibus 32-Seater',
                'official' => 'Merchandiser Commute Staff Bus',
                'driver_name' => 'Md. Monir',
                'driver_phone' => '01735827735',
                'driver_id_card' => 'EMP-DRV-1016',
                'license' => 'DL-110382-55',
                'quota' => null,
                'odo' => 112500,
                'purchase_price' => 3800000.00,
                'purchase_date' => '2021-11-05',
                'company' => $apex,
            ],
            [
                'reg' => 'DHAKA METRO-BA-11-0957',
                'type' => 'STAFF_BUS',
                'brand' => 'Hino',
                'model' => 'AK1J 52-Seater',
                'official' => 'Production Staff Commute Bus',
                'driver_name' => 'Md. Shukur',
                'driver_phone' => '01959263011',
                'driver_id_card' => 'EMP-DRV-1017',
                'license' => 'DL-110957-44',
                'quota' => null,
                'odo' => 156800,
                'purchase_price' => 4600000.00,
                'purchase_date' => '2020-08-18',
                'company' => $apex,
            ],
            [
                'reg' => 'DHAKA METRO-TA-11-3128',
                'type' => 'COVERED_VAN_5T',
                'brand' => 'Isuzu',
                'model' => 'Forward 5T Fabric Logistics',
                'official' => 'CA Knitwear Central Logistics',
                'driver_name' => 'Md. Monir',
                'driver_phone' => '01605978393',
                'driver_id_card' => 'EMP-DRV-1018',
                'license' => 'DL-113128-33',
                'quota' => null,
                'odo' => 94200,
                'purchase_price' => 2900000.00,
                'purchase_date' => '2022-09-25',
                'company' => $cakl,
            ],
            [
                'reg' => 'DHAKA METRO-TA-11-3127',
                'type' => 'COVERED_VAN_5T',
                'brand' => 'Isuzu',
                'model' => 'Forward 5T Garments Export',
                'official' => 'Finished Goods Export Van',
                'driver_name' => 'Md. Farhad',
                'driver_phone' => '01912107173',
                'driver_id_card' => 'EMP-DRV-1019',
                'license' => 'DL-113127-22',
                'quota' => null,
                'odo' => 105400,
                'purchase_price' => 2850000.00,
                'purchase_date' => '2022-10-12',
                'company' => $apex,
            ],
        ];

        foreach ($realFleet as $item) {
            $v = Vehicle::firstOrCreate(
                ['registration_no' => $item['reg']],
                [
                    'company_id' => $item['company']->id,
                    'factory_unit_id' => $unitGazipur->id,
                    'vehicle_type' => $item['type'],
                    'usage_category' => str_contains($item['type'], 'STAFF_BUS') ? 'STAFF_COMMUTE_BUS' : (str_contains($item['type'], 'COVERED_VAN') ? 'FACTORY_LOGISTICS' : (str_contains($item['type'], 'AMBULANCE') ? 'EMERGENCY_AMBULANCE' : 'DEDICATED_MANAGEMENT')),
                    'dedicated_to_official' => $item['official'],
                    'ownership_type' => 'COMPANY_OWNED',
                    'fuel_type' => str_contains($item['type'], 'VAN') || str_contains($item['type'], 'BUS') ? 'DIESEL' : 'OCTANE',
                    'fuel_payer' => $item['quota'] ? 'MONTHLY_QUOTA' : 'COMPANY',
                    'monthly_fuel_quota_liters' => $item['quota'],
                    'current_odometer' => $item['odo'],
                    'purchase_price' => $item['purchase_price'],
                    'purchase_date' => $item['purchase_date'],
                    'brand' => $item['brand'],
                    'model_name' => $item['model'],
                    'status' => 'AVAILABLE',
                ]
            );

            Driver::firstOrCreate(
                ['license_number' => $item['license']],
                [
                    'company_id' => $item['company']->id,
                    'factory_unit_id' => $unitGazipur->id,
                    'name' => $item['driver_name'],
                    'office_id_card' => $item['driver_id_card'],
                    'phone' => $item['driver_phone'],
                    'license_type' => str_contains($item['type'], 'BUS') || str_contains($item['type'], 'VAN') ? 'HEAVY' : 'LIGHT',
                    'license_expiry_date' => now()->addMonths(16),
                    'employment_type' => 'COMPANY_PAYROLL',
                    'salary' => 24000.0,
                    'current_vehicle_id' => $v->id,
                    'preferred_locale' => 'bn',
                ]
            );
        }

        // 13. Generate Sample Finalized Inter-Company Allocation Journal
        $allocationService = app(CostAllocationService::class);
        $period = now()->format('Y-m');

        if (! CostAllocation::where('company_id', $apex->id)->where('allocation_period', $period)->exists()) {
            $allocationService->generateAndSaveJournal(
                $apex->id,
                $period,
                $unitGazipur->id,
                $admin->id,
                'Monthly central transport allocation journal for N.A.Z. Bangladesh Ltd'
            );
        }

        if (! CostAllocation::where('company_id', $cakl->id)->where('allocation_period', $period)->exists()) {
            $allocationService->generateAndSaveJournal(
                $cakl->id,
                $period,
                $unitBhobanipur->id,
                $admin->id,
                'Monthly transport allocation journal for CA Knitwear Ltd'
            );
        }
    }
}
