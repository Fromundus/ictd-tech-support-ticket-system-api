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
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('mr_employeeid')->nullable()->change();
            $table->string('mr_employee_name')->nullable()->change();
            $table->unsignedBigInteger('user_employeeid')->nullable()->change();
            $table->string('user_employee_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('mr_employeeid')->nullable(false)->change();
            $table->string('mr_employee_name')->nullable(false)->change();
            $table->unsignedBigInteger('user_employeeid')->nullable(false)->change();
            $table->string('user_employee_name')->nullable(false)->change();
        });
    }
};
