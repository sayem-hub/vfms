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
        Schema::create('mobile_sync_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 100)->index();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('idempotency_key', 120)->unique();
            $table->string('action_type', 40)->index(); // TRIP_START, TRIP_END, GATE_OUT, GATE_IN, FUEL_REFILL, EXPENSE_LOG, GPS_BREADCRUMB
            $table->json('payload');
            $table->dateTime('client_recorded_at');
            $table->dateTime('synced_at')->nullable();
            $table->string('status', 30)->default('PENDING')->index(); // PENDING, SYNCED, CONFLICT, FAILED
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('server_entity_id')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_gps_pings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_request_id')->nullable()->constrained('trip_requests')->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed_kmh', 5, 2)->default(0);
            $table->decimal('heading', 5, 2)->nullable();
            $table->decimal('accuracy_meters', 6, 2)->default(0);
            $table->unsignedTinyInteger('battery_level')->nullable();
            $table->boolean('is_mock_location')->default(false);
            $table->string('nearest_geofence', 50)->nullable();
            $table->boolean('is_within_geofence')->default(false);
            $table->dateTime('recorded_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_gps_pings');
        Schema::dropIfExists('mobile_sync_outbox');
    }
};
