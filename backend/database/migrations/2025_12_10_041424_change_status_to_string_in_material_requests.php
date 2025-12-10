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
       // Change ENUM → VARCHAR (fixes all truncation errors forever)
        Schema::table('material_requests', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
        });

        // Optional: Update any existing bad data
        DB::table('material_requests')
            ->where('status', '', 0)
            ->orWhereNull('status')
            ->update(['status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending', 'manager_approved', 'manager_rejected',
                'admin_approved', 'admin_rejected', 'issued', 'returned', 'cancelled'
            ])->default('pending')->change();
        });
    }
};
