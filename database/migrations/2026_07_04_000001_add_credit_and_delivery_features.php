<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crédito por cliente: el carnicero lo habilita y asigna un cupo.
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('credit_enabled')->default(false)->after('price_adjustment_pct');
            $table->decimal('credit_limit', 12, 2)->default(0)->after('credit_enabled');
            $table->unsignedInteger('credit_terms_days')->nullable()->after('credit_limit');
        });

        // Pedido: forma de pago, estado de pago y fecha/hora de entrega obligatoria.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->default('contraentrega')->after('total'); // contraentrega|credito
            $table->string('payment_status')->default('pendiente')->after('payment_method'); // pendiente|pagado
            $table->date('payment_due_date')->nullable()->after('payment_status');
            $table->unsignedInteger('credit_terms_days')->nullable()->after('payment_due_date');
            $table->timestamp('paid_at')->nullable()->after('credit_terms_days');
            $table->dateTime('requested_at')->nullable()->after('requested_date');
        });

        // Fecha+hora arranca desde la fecha que ya existía (a las 00:00).
        DB::table('orders')->whereNotNull('requested_date')->update([
            'requested_at' => DB::raw('requested_date'),
        ]);

        // Config global: plazo de crédito por defecto y ventana de entrega.
        Schema::table('business_settings', function (Blueprint $table) {
            $table->unsignedInteger('credit_terms_days')->default(30)->after('min_order_amount');
            $table->time('delivery_start_time')->default('08:00:00')->after('credit_terms_days');
            $table->time('delivery_end_time')->default('18:00:00')->after('delivery_start_time');
            $table->unsignedInteger('delivery_min_lead_days')->default(1)->after('delivery_end_time');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['credit_enabled', 'credit_limit', 'credit_terms_days']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method', 'payment_status', 'payment_due_date',
                'credit_terms_days', 'paid_at', 'requested_at',
            ]);
        });

        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'credit_terms_days', 'delivery_start_time',
                'delivery_end_time', 'delivery_min_lead_days',
            ]);
        });
    }
};
