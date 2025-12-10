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
        Schema::create('material_requests', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('requester_id');     // employee
        $table->unsignedBigInteger('manager_id')->nullable();
        $table->unsignedBigInteger('admin_hr_id')->nullable();
        $table->unsignedBigInteger('it_staff_id')->nullable();
        $table->unsignedBigInteger('material_id');

        $table->integer('quantity')->default(1);
        $table->text('purpose')->nullable();

        $table->enum('status', [
            'pending_manager_view',
            'pending_admin_approval',
            'admin_rejected',
            'admin_approved',
            'pending_it_verification',
            'it_verified',
            'ready_for_pickup',
            'item_issued',
            'returned',
            'completed',
        ])->default('pending_manager_view');

        $table->timestamps();

        // Foreign Keys
        $table->foreign('requester_id')->references('id')->on('users');
        $table->foreign('manager_id')->references('id')->on('users');
        $table->foreign('admin_hr_id')->references('id')->on('users');
        $table->foreign('it_staff_id')->references('id')->on('users');
        $table->foreign('material_id')->references('id')->on('materials');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
