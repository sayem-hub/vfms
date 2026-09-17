<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Driver;
use App\Models\FactoryUnit;
use App\Models\FuelLog;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequisition;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCompliance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin & Accounts Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@vfms.com'],
            [
                'name' => 'Transport General Manager',
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
                'name' => 'NAZ Bangladesh Ltd',
                'address' => 'BK Bari, Gazipur',
                'phone' => '+8801711001122',
                'email' => 'info@nz-bd.com',
            ]
        );

        $echo = Company::firstOrCreate(
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
                'name' => 'Garments and Textile',
                'latitude' => 24.1850,
                'longitude' => 90.4320,
                'address' => 'BK Bari, Gazipur',
            ]
        );

        $unitNarayanganj = FactoryUnit::firstOrCreate(
            ['location_code' => 'BHBNPR'],
            [
                'company_id' => $echo->id,
                'name' => 'Garments Unit 2',
                'latitude' => 23.6850,
                'longitude' => 90.5120,
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

        // 6. Create Sample Trip Requisition with ERP Cross-References
        $trip = TripRequisition::firstOrCreate(
            ['requisition_no' => 'REQ-202609-001'],
            [
                'company_id' => $apex->id,
                'factory_unit_id' => $unitGazipur->id,
                'requester_id' => $admin->id,
                'vehicle_id' => $microbus->id,
                'driver_id' => $driverRafiq->id,
                'trip_type' => 'OFFICIAL_DUTY',
                'purpose' => 'Management Audit & Buyer Compliance Visit at Mawna Unit',
                'origin_name' => 'Corporate Head Office, Gulshan-2, Dhaka',
                'destination_name' => 'Mawna Unit 1 Factory, Gazipur',
                'origin_latitude' => 23.7925,
                'origin_longitude' => 90.4078,
                'destination_latitude' => 24.1850,
                'destination_longitude' => 90.4320,
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
                'erp_requisition_no' => 'ERP-INDENT-98124',
                'erp_gatepass_no' => 'GP-GZP-2026-4412',
                'status' => 'COMPLETED',
            ]
        );

        // 7. Create Fuel Refill & Trip Expense Settlement
        FuelLog::firstOrCreate(
            ['odometer_reading' => 45200],
            [
                'trip_requisition_id' => $trip->id,
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
            ['trip_requisition_id' => $trip->id],
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
    }
}
