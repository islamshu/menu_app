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
        Schema::create('restaurant_brandings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->json('published')->nullable();
            $table->json('draft')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_brandings');
    }
};
