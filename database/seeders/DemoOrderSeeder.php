<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Support\Sequences;
use Illuminate\Database\Seeder;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::query()->approved()->first() ?? Customer::query()->first();
        if (! $customer) {
            return;
        }

        // Evita duplicar en re-seed.
        if ($customer->orders()->exists()) {
            return;
        }

        $variants = ProductVariant::query()
            ->where('is_available', true)
            ->with('product')
            ->take(5)
            ->get();

        if ($variants->isEmpty()) {
            return;
        }

        $order = new Order([
            'order_number' => Sequences::nextOrderNumber(),
            'status' => 'pendiente',
            'price_adjustment_pct' => $customer->price_adjustment_pct,
            'delivery_business_name' => $customer->business_name,
            'delivery_contact_name' => $customer->contact_name,
            'delivery_phone' => $customer->phone,
            'delivery_address_line1' => $customer->address_line1,
            'delivery_address_line2' => $customer->address_line2,
            'delivery_city' => $customer->city,
            'delivery_state' => $customer->state,
            'delivery_zip' => $customer->zip,
            'delivery_notes' => 'Entregar por la mañana. (pedido demo)',
            'shipping_fee' => 0,
            'tax' => 0,
            'placed_at' => now(),
        ]);
        $order->customer()->associate($customer);
        $order->save();

        $qtys = [150, 79, 30, 25, 60];
        $subtotal = 0;

        foreach ($variants as $i => $variant) {
            $qty = $qtys[$i] ?? 20;
            $unitPrice = $customer->priceFor($variant);
            $amount = round($qty * $unitPrice, 2);
            $subtotal += $amount;

            $order->items()->create([
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name_es,
                'variant_label' => $variant->label_es,
                'unit' => $variant->unit,
                'unit_label' => $variant->unit_label_es,
                'quantity' => $qty,
                'base_price' => $variant->base_price,
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ]);
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    }
}
