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
        Schema::table('material_request_actions', function (Blueprint $table) {
            $table->string('action_type', 50)->change(); // Now accepts any string
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_request_actions', function (Blueprint $table) {
            $table->enum('action_type', ['approved', 'rejected', 'comment', 'issued', 'returned'])
                  ->change();
        });
    }
};
