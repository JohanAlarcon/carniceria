<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name_es');
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->string('english_cut')->nullable(); // ej. "chuck roll"
            $table->text('description_es')->nullable();
            $table->text('description_en')->nullable();
            $table->string('icon')->nullable();  // icono generado (svg/webp)
            $table->string('image')->nullable(); // foto real subida
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
