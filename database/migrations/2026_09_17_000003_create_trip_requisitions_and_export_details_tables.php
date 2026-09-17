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
        Schema::create('trip_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_no', 40)->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('factory_unit_id')->nullable()->constrained('factory_units')->nullOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->enum('trip_type', [
                'OFFICIAL_DUTY',
                'EMPLOYEE_COMMUTE',
                'EXPORT_SHIPMENT',
                'EMERGENCY_AMBULANCE',
                'GUEST_QC_PICKUP',
                'PERSONAL_USE',
            ])->default('OFFICIAL_DUTY');
            $table->text('purpose')->nullable();
            $table->string('origin_name');
            $table->string('destination_name');
            $table->decimal('origin_latitude', 10, 7)->nullable();
            $table->decimal('origin_longitude', 10, 7)->nullable();
            $table->decimal('destination_latitude', 10, 7)->nullable();
            $table->decimal('destination_longitude', 10, 7)->nullable();
            $table->dateTime('scheduled_start_time');
            $table->dateTime('scheduled_end_time')->nullable();
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->unsignedInteger('start_odometer')->nullable();
            $table->unsignedInteger('end_odometer')->nullable();
            $table->decimal('claimed_distance_km', 8, 2)->nullable();
            $table->decimal('expected_distance_km', 8, 2)->nullable();
            $table->decimal('distance_variance_percentage', 5, 2)->nullable();
            $table->boolean('is_distance_anomaly')->default(false);
            $table->text('anomaly_justification')->nullable();
            $table->foreignId('anomaly_reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            // Custom ERP Cross-Reference & Sync Fields
            $table->string('erp_requisition_no', 60)->nullable()->index();
            $table->string('erp_requisition_copy')->nullable();
            $table->string('erp_gatepass_no', 60)->nullable()->index();
            $table->string('erp_gatepass_copy')->nullable();
            $table->enum('erp_sync_status', ['NOT_SYNCED', 'PENDING', 'SYNCED', 'FAILED'])->default('NOT_SYNCED');
            $table->timestamp('erp_synced_at')->nullable();
            $table->json('erp_sync_payload')->nullable();

            $table->enum('status', [
                'SUBMITTED',
                'HOD_APPROVED',
                'DISPATCHED',
                'GATE_OUT',
                'IN_TRIP',
                'GATE_IN',
                'COMPLETED',
                'CANCELLED',
                'REJECTED',
            ])->default('SUBMITTED');
            $table->timestamps();
        });

        Schema::create('export_shipment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_requisition_id')->constrained('trip_requisitions')->cascadeOnDelete();
            $table->string('buyer_name', 100);
            $table->string('export_lc_no', 100)->nullable();
            $table->string('commercial_invoice_no', 100)->nullable();
            $table->unsignedInteger('carton_quantity')->default(0);
            $table->decimal('cbm_volume', 8, 2)->nullable();
            $table->string('destination_port_offdock', 150);
            $table->string('cf_agent_name', 100)->nullable();
            $table->dateTime('port_arrival_time')->nullable();
            $table->dateTime('port_release_time')->nullable();
            $table->decimal('waiting_hours', 6, 2)->default(0);
            $table->decimal('free_waiting_hours_allowed', 4, 2)->default(24.00);
            $table->decimal('demurrage_charge_per_hour', 8, 2)->default(0);
            $table->decimal('total_demurrage_payable', 10, 2)->default(0);
            $table->string('erp_challan_no', 60)->nullable()->index();
            $table->string('erp_challan_copy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_shipment_details');
        Schema::dropIfExists('trip_requisitions');
    }
};
