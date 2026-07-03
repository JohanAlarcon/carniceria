<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');           // snapshot del nombre
            $table->string('variant_label')->nullable();
            $table->string('unit')->default('lb');
            $table->string('unit_label')->default('lb');
            $table->decimal('quantity', 10, 2);
            $table->decimal('base_price', 10, 2);     // snapshot precio base
            $table->decimal('unit_price', 10, 2);     // precio tras % del cliente
            $table->decimal('amount', 12, 2);         // quantity * unit_price
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
