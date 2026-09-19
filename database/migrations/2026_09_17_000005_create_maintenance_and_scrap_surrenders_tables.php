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
            $table->string('pre_requisition_no', 40)->nullable()->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('transport_requester_id')->nullable()->constrained('users')->nullOnDelete();

            // 1. Digital Pre-Requisition & Admin Head Digital Approval
            $table->foreignId('admin_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('admin_approval_status', [
                'DRAFT',
                'PENDING_ADMIN_APPROVAL',
                'APPROVED_BY_ADMIN',
                'REJECTED_BY_ADMIN',
            ])->default('PENDING_ADMIN_APPROVAL');
            $table->dateTime('admin_approved_at')->nullable();
            $table->text('admin_remarks')->nullable();

            $table->enum('maintenance_type', [
                'SCHEDULED_PREVENTIVE',
                'EMERGENCY_BREAKDOWN',
                'ACCIDENT_BODYWORK',
                'TYRE_BATTERY_REPLACEMENT',
                'AC_SERVICING',
                'ELECTRICAL_EFI',
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
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->decimal('parts_total_cost', 10, 2)->default(0);
            $table->decimal('labor_total_cost', 10, 2)->default(0);
            $table->decimal('grand_total_cost', 10, 2)->default(0);

            // 2. ERP Requisition Tagging (Created by Store post-approval, tagged by Transport)
            $table->enum('erp_requisition_type', [
                'SERVICE_REQUISITION',
                'PARTS_REQUISITION',
            ])->default('SERVICE_REQUISITION');
            $table->string('erp_requisition_no', 60)->nullable()->index();
            $table->date('erp_requisition_date')->nullable();
            $table->string('erp_requisition_copy')->nullable();
            $table->dateTime('erp_requisition_tagged_at')->nullable();
            $table->foreignId('erp_requisition_tagged_by')->nullable()->constrained('users')->nullOnDelete();

            // 3. ERP Gate Pass (For sending parts to external vendor to repair)
            $table->boolean('needs_vendor_repair_gatepass')->default(false);
            $table->string('erp_gatepass_no', 60)->nullable()->index();
            $table->enum('erp_gatepass_type', [
                'RETURNABLE_GATE_PASS',
                'NON_RETURNABLE_GATE_PASS',
            ])->default('RETURNABLE_GATE_PASS');
            $table->string('erp_gatepass_copy')->nullable();
            $table->dateTime('parts_sent_to_vendor_at')->nullable();
            $table->dateTime('parts_returned_from_vendor_at')->nullable();

            // 4. Central Store Scrap Surrender & Payment Clearance Control
            $table->boolean('requires_old_parts_surrender')->default(true);
            $table->boolean('is_old_parts_surrendered')->default(false);
            $table->foreignId('store_acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('store_acknowledged_at')->nullable();

            $table->enum('status', [
                'PENDING_ADMIN_APPROVAL',
                'ADMIN_APPROVED_AWAITING_ERP',
                'ERP_REQ_TAGGED',
                'PARTS_SENT_TO_VENDOR',
                'UNDER_SERVICE',
                'PENDING_PARTS_SURRENDER',
                'COMPLETED',
                'CANCELLED',
                'REJECTED',
            ])->default('PENDING_ADMIN_APPROVAL');

            $table->enum('payment_status', [
                'PENDING_ADMIN_APPROVAL',
                'PENDING_PARTS_SURRENDER',
                'READY_FOR_PAYMENT',
                'PAID',
                'CANCELLED',
            ])->default('PENDING_ADMIN_APPROVAL');

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
