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
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_requisition_id')->nullable()->constrained('trip_requisitions')->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->enum('fuel_type', [
                'DIESEL',
                'OCTANE',
                'PETROL',
                'CNG',
                'LPG',
            ]);
            $table->dateTime('refill_date');
            $table->string('station_name', 150);
            $table->unsignedInteger('odometer_reading');
            $table->decimal('fuel_quantity', 8, 2); // Liters or m3
            $table->decimal('unit_price', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->enum('payment_method', [
                'PETTY_CASH_ADVANCE',
                'DRIVER_POCKET_REIMBURSABLE',
                'COMPANY_CREDIT_VOUCHER',
            ])->default('PETTY_CASH_ADVANCE');
            $table->unsignedInteger('km_since_last_refill')->nullable();
            $table->decimal('calculated_km_per_liter', 5, 2)->nullable();
            $table->boolean('is_efficiency_anomaly')->default(false);

            // Mandatory 3-Point Photo Proof
            $table->string('dispenser_photo')->nullable();
            $table->string('odometer_photo')->nullable();
            $table->string('receipt_memo_photo')->nullable();

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('trip_expense_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_requisition_id')->unique()->constrained('trip_requisitions')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->decimal('advance_cash_received', 10, 2)->default(0);
            $table->foreignId('advance_received_from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('advance_disbursed_at')->nullable();

            $table->decimal('total_fuel_expense', 10, 2)->default(0);
            $table->decimal('total_toll_expense', 10, 2)->default(0);
            $table->decimal('total_parking_expense', 8, 2)->default(0);
            $table->decimal('total_driver_food_allowance', 8, 2)->default(0);
            $table->decimal('total_emergency_repair_expense', 10, 2)->default(0);
            $table->decimal('total_other_expense', 8, 2)->default(0);
            $table->decimal('total_actual_expense', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2)->default(0); // advance - actual

            $table->string('receipts_attachment')->nullable();
            $table->foreignId('cashier_settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('settled_at')->nullable();
            $table->enum('status', [
                'DRAFT_BY_DRIVER',
                'AUDITED_BY_TRANSPORT',
                'SETTLED_BY_CASHIER',
                'DISPUTED',
            ])->default('DRAFT_BY_DRIVER');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_expense_settlements');
        Schema::dropIfExists('fuel_logs');
    }
};
