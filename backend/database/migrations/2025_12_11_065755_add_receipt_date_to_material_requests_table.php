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
        Schema::table('material_requests', function (Blueprint $table) {
            // Step 1: Add the column as NULLABLE first
            $table->date('receipt_date')
                  ->nullable()
                  ->after('quantity')
                  ->comment('Date when the material is required by the requester');
        });

        // Step 2: Fill all existing rows with today's date (or any valid date)
        DB::table('material_requests')
          ->whereNull('receipt_date')
          ->update(['receipt_date' => now()->toDateString()]);

        // Step 3: Now make it NOT NULL (safe because all rows have values)
        Schema::table('material_requests', function (Blueprint $table) {
            $table->date('receipt_date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn('receipt_date');
        });
    }
};
