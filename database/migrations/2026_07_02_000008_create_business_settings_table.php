<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('Carnicería');
            $table->string('legal_name')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->default('U.S.A.');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('invoice_prefix')->default('INV-');
            $table->unsignedInteger('invoice_next_number')->default(1);
            $table->unsignedInteger('order_next_number')->default(1);
            $table->text('invoice_notes_es')->nullable();
            $table->text('invoice_notes_en')->nullable();
            $table->text('invoice_terms_es')->nullable();
            $table->text('invoice_terms_en')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->boolean('free_delivery')->default(true);
            $table->decimal('default_shipping_fee', 12, 2)->default(0);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
