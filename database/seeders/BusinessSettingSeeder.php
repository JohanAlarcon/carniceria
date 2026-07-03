<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run(): void
    {
        BusinessSetting::updateOrCreate(
            ['id' => 1],
            [
                'business_name' => 'Mi Carnicería',
                'legal_name' => null,
                'address_line1' => '000 Example Ave',
                'address_line2' => null,
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
                'country' => 'U.S.A.',
                'phone' => '(000) 000-0000',
                'email' => 'pedidos@micarniceria.com',
                'website' => null,
                'tax_id' => null,
                'invoice_prefix' => 'INV-',
                'invoice_next_number' => 1,
                'order_next_number' => 1,
                'currency' => 'USD',
                'free_delivery' => true,
                'default_shipping_fee' => 0,
                'min_order_amount' => null,
                'invoice_notes_es' => '¡Gracias por su preferencia y por confiar en nosotros!',
                'invoice_notes_en' => 'Thank you for your business and trust!',
                'invoice_terms_es' => "Reclamos: Reportar al chofer al recibir o llamar dentro de 48 horas. El producto debe estar en su empaque original, completo y con etiquetas.\n"
                    ."Retiros en Planta: Todo producto recogido debe ser inspeccionado al momento. No se aceptan cambios ni reclamos una vez la mercancía abandone la planta.\n"
                    ."Créditos y Peso: Las aprobaciones de crédito se basarán en el peso devuelto y verificado bajo normas USDA.\n"
                    .'Pagos con Cheque: Al pagar con cheque, autoriza un cobro electrónico único de su cuenta el mismo día.',
                'invoice_terms_en' => "Claims: Report to the driver upon receipt or call within 48 hours. Product must be in its original packaging, complete and with labels.\n"
                    ."Pick-Ups: Products picked up at our facility must be inspected on-site. No returns or claims allowed once the product leaves the plant.\n"
                    ."Credits & Weight: Credit approvals are subject to returned weight verification under USDA regulations.\n"
                    .'Check Payments: Paying by check authorizes a one-time electronic fund transfer on the same day.',
            ]
        );
    }
}
