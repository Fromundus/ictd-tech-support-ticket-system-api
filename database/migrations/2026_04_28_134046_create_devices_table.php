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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mr_employeeid');
            $table->string('mr_employee_name');
            $table->unsignedBigInteger('user_employeeid');
            $table->string('user_employee_name');
            $table->string('name')->nullable();
            $table->string('type');
            $table->string('custom_type')->nullable();
            $table->string('brand');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();

            $table->string('processor')->nullable();
            $table->string('ram')->nullable();
            $table->string('system_type')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('storage')->nullable();
            $table->string('mac_address')->nullable();

            $table->unsignedBigInteger('created_by_employeeid');
            $table->unsignedBigInteger('updated_by_employeeid')->nullable();

            $table->date('purchased_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
