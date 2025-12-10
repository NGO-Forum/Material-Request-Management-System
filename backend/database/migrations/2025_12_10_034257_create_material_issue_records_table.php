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
        Schema::create('material_issue_records', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('request_id');
        $table->unsignedBigInteger('issued_by'); // admin/hr or IT

        $table->timestamp('issued_date')->useCurrent();
        $table->date('expected_return_date')->nullable();
        $table->date('actual_return_date')->nullable();

        $table->timestamps();

        // Foreign Keys
        $table->foreign('request_id')->references('id')->on('material_requests')->onDelete('cascade');
        $table->foreign('issued_by')->references('id')->on('users');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_issue_records');
    }
};
