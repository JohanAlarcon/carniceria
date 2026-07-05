<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unidad de la anticipación mínima: 'days' (por defecto) u 'hours'.
        // El número sigue en delivery_min_lead_days (se interpreta según la unidad).
        Schema::table('business_settings', function (Blueprint $table) {
            $table->string('delivery_min_lead_unit', 10)->default('days')->after('delivery_min_lead_days');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn('delivery_min_lead_unit');
        });
    }
};
