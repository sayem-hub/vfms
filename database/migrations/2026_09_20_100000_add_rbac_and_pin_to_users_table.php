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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'ADMIN',
                'TRANSPORT_OFFICER',
                'SECURITY_GUARD',
                'DRIVER',
                'EMPLOYEE',
            ])->default('EMPLOYEE')->after('email');

            $table->string('employee_id', 50)->nullable()->unique()->after('role');
            $table->string('phone', 30)->nullable()->index()->after('employee_id');
            $table->string('pin')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'employee_id', 'phone', 'pin', 'is_active']);
        });
    }
};
