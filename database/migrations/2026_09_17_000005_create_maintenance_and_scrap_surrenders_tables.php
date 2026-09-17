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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_no', 40)->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('maintenance_type', [
                'SCHEDULED_PREVENTIVE',
                'EMERGENCY_BREAKDOWN',
                'ACCIDENT_BODYWORK',
                'TYRE_BATTERY_REPLACEMENT',
            ])->default('SCHEDULED_PREVENTIVE');
            $table->enum('workshop_type', [
                'FACTORY_IN_HOUSE_WORKSHOP',
                'EXTERNAL_VENDOR_GARAGE',
            ])->default('FACTORY_IN_HOUSE_WORKSHOP');
            $table->string('vendor_name', 150)->nullable();
            $table->string('vendor_phone', 30)->nullable();
            $table->text('vendor_address')->nullable();
            $table->unsignedInteger('odometer_at_service');
            $table->date('service_date');
            $table->text('service_description');
            $table->decimal('parts_total_cost', 10, 2)->default(0);
            $table->decimal('labor_total_cost', 10, 2)->default(0);
            $table->decimal('grand_total_cost', 10, 2);
            $table->boolean('requires_old_parts_surrender')->default(true);
            $table->boolean('is_old_parts_surrendered')->default(false);
            $table->foreignId('store_acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('store_acknowledged_at')->nullable();

            // Custom ERP Cross-Reference
            $table->string('erp_pr_po_no', 60)->nullable()->index();
            $table->string('erp_document_copy')->nullable();

            $table->enum('payment_status', [
                'PENDING_PARTS_SURRENDER',
                'READY_FOR_PAYMENT',
                'PAID',
                'CANCELLED',
            ])->default('PENDING_PARTS_SURRENDER');
            $table->timestamps();
        });

        Schema::create('scrap_parts_surrenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_record_id')->constrained('maintenance_records')->cascadeOnDelete();
            $table->foreignId('factory_unit_id')->constrained('factory_units')->cascadeOnDelete();
            $table->string('item_name', 150);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('item_serial_or_code', 100)->nullable();
            $table->string('photo_of_scrap_part')->nullable();
            $table->foreignId('received_by_store_officer_id')->constrained('users')->cascadeOnDelete();
            $table->string('scrap_bin_location', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrap_parts_surrenders');
        Schema::dropIfExists('maintenance_records');
    }
};
