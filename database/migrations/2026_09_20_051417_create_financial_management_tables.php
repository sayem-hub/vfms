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
        // Add capital purchase price and purchase date to vehicles for TCO calculation
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('purchase_price', 12, 2)->nullable()->after('rate_per_km');
            $table->date('purchase_date')->nullable()->after('purchase_price');
        });

        // Inter-Company Cost Allocation Journal table
        Schema::create('cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('factory_unit_id')->nullable()->constrained('factory_units')->nullOnDelete();
            $table->string('allocation_period', 20)->index(); // e.g. "2026-09"
            $table->string('journal_reference_no', 50)->unique(); // e.g. "JRN-202609-001"
            $table->integer('total_trips_count')->default(0);
            $table->decimal('total_km_run', 10, 2)->default(0);
            $table->decimal('total_fuel_cost', 12, 2)->default(0);
            $table->decimal('total_maintenance_cost', 12, 2)->default(0);
            $table->decimal('total_driver_cost', 12, 2)->default(0);
            $table->decimal('total_toll_and_operating_cost', 12, 2)->default(0);
            $table->decimal('grand_total_allocated', 12, 2)->default(0);
            $table->enum('status', ['DRAFT', 'FINALIZED', 'POSTED_TO_ERP'])->default('DRAFT');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Detailed vehicle-wise itemized allocation within the journal
        Schema::create('cost_allocation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_allocation_id')->constrained('cost_allocations')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->integer('trips_count')->default(0);
            $table->decimal('km_run', 10, 2)->default(0);
            $table->decimal('fuel_cost', 10, 2)->default(0);
            $table->decimal('maintenance_cost', 10, 2)->default(0);
            $table->decimal('operating_cost', 10, 2)->default(0);
            $table->decimal('total_allocated_cost', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_allocation_items');
        Schema::dropIfExists('cost_allocations');

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'purchase_date']);
        });
    }
};
