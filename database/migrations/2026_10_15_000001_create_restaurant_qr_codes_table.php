<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_qr_codes', function (Blueprint $table) {
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

    public function down(): void
    {
        Schema::dropIfExists('restaurant_qr_codes');
    }
};
