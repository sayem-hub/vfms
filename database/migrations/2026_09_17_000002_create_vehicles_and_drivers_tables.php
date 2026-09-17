<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('factory_unit_id')->nullable()->constrained('factory_units')->nullOnDelete();
            $table->string('registration_no', 50)->unique();
            $table->enum('vehicle_type', [
                'SEDAN_CAR',
                'MICROBUS',
                'STAFF_BUS',
                'COVERED_VAN_2T',
                'COVERED_VAN_5T',
                'COVERED_VAN_10T',
                'AMBULANCE',
                'MOTORCYCLE',
                'PICKUP',
                'OTHER',
            ])->default('SEDAN_CAR');
            $table->enum('ownership_type', [
                'COMPANY_OWNED',
                'EXECUTIVE_CAR_SCHEME',
                'RENTED_VENDOR',
                'EMPLOYEE_PERSONAL',
            ])->default('COMPANY_OWNED');
            $table->enum('fuel_type', [
                'DIESEL',
                'OCTANE',
                'PETROL',
                'CNG',
                'LPG',
                'DUAL_OCTANE_CNG',
                'DUAL_OCTANE_LPG',
            ])->default('OCTANE');
            $table->enum('fuel_payer', [
                'COMPANY',
                'EMPLOYEE',
                'VENDOR',
                'MONTHLY_QUOTA',
            ])->default('COMPANY');
            $table->enum('maintenance_payer', [
                'COMPANY',
                'EMPLOYEE',
                'VENDOR',
                'SHARED_POLICY',
            ])->default('COMPANY');
            $table->enum('driver_payer', [
                'COMPANY',
                'EMPLOYEE',
                'VENDOR',
            ])->default('COMPANY');
            $table->decimal('monthly_fuel_quota_liters', 8, 2)->nullable();
            $table->decimal('monthly_fixed_cost', 12, 2)->default(0);
            $table->decimal('rate_per_km', 8, 2)->nullable();
            $table->unsignedInteger('current_odometer')->default(0);
            $table->decimal('fuel_capacity_liters', 8, 2)->default(50);
            $table->decimal('expected_km_per_liter', 5, 2)->default(8.0);
            $table->string('brand', 50)->nullable();
            $table->string('model_name', 50)->nullable();
            $table->string('model_year', 10)->nullable();
            $table->string('chassis_number', 100)->nullable();
            $table->string('engine_number', 100)->nullable();
            $table->enum('status', [
                'AVAILABLE',
                'ON_TRIP',
                'UNDER_MAINTENANCE',
                'ACCIDENT_GROUNDED',
                'DECOMMISSIONED',
            ])->default('AVAILABLE');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('factory_unit_id')->nullable()->constrained('factory_units')->nullOnDelete();
            $table->string('name');
            $table->string('office_id_card', 50)->nullable()->unique();
            $table->string('phone', 30)->index();
            $table->string('nid_number', 50)->nullable();
            $table->string('photo')->nullable();
            $table->string('license_number', 50)->unique();
            $table->enum('license_type', ['LIGHT', 'MEDIUM', 'HEAVY', 'MOTORCYCLE'])->default('LIGHT');
            $table->date('license_expiry_date');
            $table->string('license_scanned_copy')->nullable();
            $table->enum('employment_type', [
                'COMPANY_PAYROLL',
                'VENDOR_DRIVER',
                'PERSONAL_DRIVER',
                'DAILY_WAGE',
            ])->default('COMPANY_PAYROLL');
            $table->decimal('salary', 12, 2)->nullable();
            $table->foreignId('current_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('preferred_locale', 5)->default('bn');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vehicle_compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('document_type', [
                'FITNESS_CERTIFICATE',
                'TAX_TOKEN',
                'ROUTE_PERMIT',
                'INSURANCE_CERTIFICATE',
                'CNG_CYLINDER_TEST',
                'POLLUTION_TEST',
            ]);
            $table->string('certificate_number', 100);
            $table->date('issue_date');
            $table->date('expiry_date')->index();
            $table->string('document_attachment')->nullable();
            $table->decimal('renewal_cost', 10, 2)->nullable();
            $table->timestamp('alert_60d_sent_at')->nullable();
            $table->timestamp('alert_30d_sent_at')->nullable();
            $table->timestamp('alert_15d_sent_at')->nullable();
            $table->timestamp('alert_7d_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_compliances');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
    }
};
