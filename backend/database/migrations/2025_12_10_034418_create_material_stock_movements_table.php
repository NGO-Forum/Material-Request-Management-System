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
         Schema::create('material_stock_movements', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('material_id');
        $table->unsignedBigInteger('request_id')->nullable();

        $table->enum('movement_type', ['issue', 'return']);
        $table->integer('quantity')->default(1);
        $table->text('remarks')->nullable();

        $table->timestamp('created_at')->useCurrent();

        // Foreign Keys
        $table->foreign('material_id')->references('id')->on('materials');
        $table->foreign('request_id')->references('id')->on('material_requests')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_stock_movements');
    }
};
