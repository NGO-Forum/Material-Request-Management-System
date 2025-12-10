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
        Schema::create('material_returns', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('request_id');
        $table->unsignedBigInteger('returned_by');
        $table->timestamp('return_date')->useCurrent();

        $table->unsignedBigInteger('it_inspected_by')->nullable();
        $table->enum('it_condition_status', ['good', 'damaged', 'lost'])->default('good');
        $table->text('it_remarks')->nullable();

        $table->unsignedBigInteger('final_confirmed_by')->nullable();
        $table->text('admin_remarks')->nullable();

        $table->timestamps();

        // Foreign Keys
        $table->foreign('request_id')->references('id')->on('material_requests')->onDelete('cascade');
        $table->foreign('returned_by')->references('id')->on('users');
        $table->foreign('it_inspected_by')->references('id')->on('users');
        $table->foreign('final_confirmed_by')->references('id')->on('users');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_returns');
    }
};
