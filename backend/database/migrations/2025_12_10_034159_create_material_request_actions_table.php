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
        Schema::create('material_request_actions', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('request_id');
        $table->unsignedBigInteger('action_by');

        $table->enum('action_type', [
            'manager_viewed',
            'admin_approved',
            'admin_rejected',
            'it_verified',
            'issued_to_user',
            'return_initiated',
            'it_inspected',
            'admin_return_confirmed',
        ]);

        $table->text('remarks')->nullable();

        $table->timestamp('created_at')->useCurrent();

        // Foreign Keys
        $table->foreign('request_id')->references('id')->on('material_requests')->onDelete('cascade');
        $table->foreign('action_by')->references('id')->on('users');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_request_actions');
    }
};
