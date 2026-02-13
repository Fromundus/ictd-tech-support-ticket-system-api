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
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('requested_by_employeeid')->after('id')->nullable();
            $table->text('uid')->after('requested_by_employeeid')->nullable();
            $table->unsignedBigInteger('tech_employeeid')->after('uid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
             $table->dropColumn([
                'requested_by_employeeid',
                'uid',
                'tech_employeeid',
            ]);
        });
    }
};
