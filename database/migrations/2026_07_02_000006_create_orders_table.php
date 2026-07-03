<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('pendiente'); // pendiente|confirmado|en_preparacion|en_ruta|entregado|cancelado
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('price_adjustment_pct', 6, 2)->default(0); // snapshot del % del cliente

            // Snapshot de entrega
            $table->string('delivery_business_name')->nullable();
            $table->string('delivery_contact_name')->nullable();
            $table->string('delivery_phone')->nullable();
            $table->string('delivery_address_line1')->nullable();
            $table->string('delivery_address_line2')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_state')->nullable();
            $table->string('delivery_zip')->nullable();
            $table->date('requested_date')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->string('invoice_number')->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
