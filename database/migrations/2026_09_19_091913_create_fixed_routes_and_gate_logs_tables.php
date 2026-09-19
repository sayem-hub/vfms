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
        // Add usage category & dedication to vehicles
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('usage_category', 50)->default('GENERAL_POOL')->after('vehicle_type');
            $table->string('dedicated_to_official', 150)->nullable()->after('usage_category');
        });

        // Fixed Routes for staff commute buses, shuttles & regular trips
        Schema::create('fixed_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('factory_unit_id')->nullable()->constrained('factory_units')->nullOnDelete();
            $table->string('route_name');
            $table->string('route_code', 30)->nullable();
            $table->string('origin_name');
            $table->string('destination_name');
            $table->decimal('standard_distance_km', 8, 2)->nullable();
            $table->string('scheduled_departure_time', 20)->nullable(); // e.g. "06:30 AM"
            $table->string('scheduled_return_time', 20)->nullable();    // e.g. "06:30 PM"
            $table->string('shift_name', 50)->default('General Shift');
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('assigned_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('stoppages')->nullable(); // description of stops along the route
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Daily Vehicle Gate Logs for fixed commute, dedicated management, maintenance test runs
        Schema::create('vehicle_gate_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('fixed_route_id')->nullable()->constrained('fixed_routes')->nullOnDelete();
            $table->string('log_type', 50)->default('DEDICATED_MANAGEMENT_CAR'); // DEDICATED_MANAGEMENT_CAR, STAFF_COMMUTE_BUS, MAINTENANCE_TEST_RUN, OFFICIAL_DUTY, OTHER
            $table->date('log_date')->index();
            $table->dateTime('gate_out_time')->nullable();
            $table->dateTime('gate_in_time')->nullable();
            $table->unsignedInteger('out_odometer')->nullable();
            $table->unsignedInteger('in_odometer')->nullable();
            $table->decimal('total_km', 8, 2)->nullable();
            $table->string('official_name', 150)->nullable(); // MD Sir, Director SCM, GM HR, etc.
            $table->string('destination', 150)->nullable();
            $table->string('purpose', 255)->nullable();
            $table->string('status', 30)->default('OUT'); // OUT, COMPLETED, CANCELLED
            $table->foreignId('security_guard_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_gate_logs');
        Schema::dropIfExists('fixed_routes');

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['usage_category', 'dedicated_to_official']);
        });
    }
};
