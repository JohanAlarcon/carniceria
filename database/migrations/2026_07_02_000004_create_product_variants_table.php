<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label_es');             // "EXCEL Choice Angus 86E"
            $table->string('label_en')->nullable();
            $table->string('brand')->nullable();    // EXCEL, JBS, National, Sukarne...
            $table->string('grade')->nullable();    // Choice, Select, Angus, AA, AAA
            $table->boolean('is_frozen')->default(false);
            $table->string('sku')->nullable();
            $table->string('unit')->default('lb');  // lb, box, piece, bag, hank
            $table->string('unit_label_es')->default('lb');
            $table->string('unit_label_en')->default('lb');
            $table->decimal('package_weight_lb', 8, 2)->nullable();
            $table->decimal('base_price', 10, 2)->default(0); // precio por unidad
            $table->boolean('is_available')->default(true);    // OUT => false
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
