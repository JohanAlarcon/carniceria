<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Support\Sequences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $customer = $request->user()->customer;

        $orders = $customer
            ? $customer->orders()->withCount('items')->latest('placed_at')->get()->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'status_label' => $o->statusLabel(),
                'status_color' => $o->statusColor(),
                'total' => (float) $o->total,
                'items_count' => $o->items_count,
                'placed_at' => optional($o->placed_at)->toIso8601String(),
                'requested_date' => optional($o->requested_date)->toDateString(),
            ])->values()
            : collect();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->customer_id === $request->user()->customer?->id, 403);

        $order->load('items');

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->statusLabel(),
                'status_color' => $order->statusColor(),
                'subtotal' => (float) $order->subtotal,
                'shipping_fee' => (float) $order->shipping_fee,
                'total' => (float) $order->total,
                'placed_at' => optional($order->placed_at)->toIso8601String(),
                'requested_date' => optional($order->requested_date)->toDateString(),
                'delivery_address_line1' => $order->delivery_address_line1,
                'delivery_address_line2' => $order->delivery_address_line2,
                'delivery_city' => $order->delivery_city,
                'delivery_state' => $order->delivery_state,
                'delivery_zip' => $order->delivery_zip,
                'delivery_notes' => $order->delivery_notes,
                'items' => $order->items->map(fn ($it) => [
                    'product_name' => $it->product_name,
                    'variant_label' => $it->variant_label,
                    'quantity' => (float) $it->quantity,
                    'unit_label' => $it->unit_label,
                    'unit_price' => (float) $it->unit_price,
                    'amount' => (float) $it->amount,
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer && $customer->is_approved, 403, 'Tu cuenta aún no está aprobada.');

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'delivery_business_name' => 'nullable|string|max:255',
            'delivery_contact_name' => 'nullable|string|max:255',
            'delivery_phone' => 'nullable|string|max:255',
            'delivery_address_line1' => 'required|string|max:255',
            'delivery_address_line2' => 'nullable|string|max:255',
            'delivery_city' => 'required|string|max:255',
            'delivery_state' => 'nullable|string|max:255',
            'delivery_zip' => 'nullable|string|max:255',
            'requested_date' => 'nullable|date|after_or_equal:today',
            'delivery_notes' => 'nullable|string|max:1000',
        ]);

        $variants = ProductVariant::with('product')
            ->whereIn('id', collect($data['items'])->pluck('variant_id'))
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        abort_if($variants->isEmpty(), 422, 'No hay productos disponibles en el pedido.');

        $order = DB::transaction(function () use ($customer, $data, $variants) {
            $order = new Order([
                'order_number' => Sequences::nextOrderNumber(),
                'status' => 'pendiente',
                'price_adjustment_pct' => $customer->price_adjustment_pct,
                'delivery_business_name' => $data['delivery_business_name'] ?? $customer->business_name,
                'delivery_contact_name' => $data['delivery_contact_name'] ?? $customer->contact_name,
                'delivery_phone' => $data['delivery_phone'] ?? $customer->phone,
                'delivery_address_line1' => $data['delivery_address_line1'],
                'delivery_address_line2' => $data['delivery_address_line2'] ?? null,
                'delivery_city' => $data['delivery_city'],
                'delivery_state' => $data['delivery_state'] ?? null,
                'delivery_zip' => $data['delivery_zip'] ?? null,
                'requested_date' => $data['requested_date'] ?? null,
                'delivery_notes' => $data['delivery_notes'] ?? null,
                'shipping_fee' => 0,
                'tax' => 0,
                'placed_at' => now(),
            ]);
            $order->customer()->associate($customer);
            $order->save();

            $subtotal = 0;
            foreach ($data['items'] as $line) {
                $variant = $variants->get($line['variant_id']);
                if (! $variant) {
                    continue;
                }
                $qty = round((float) $line['quantity'], 2);
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

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            return $order;
        });

        // Tiempo real al panel (si Reverb está corriendo). No debe romper el pedido si falla.
        try {
            broadcast(new OrderCreated($order->loadMissing('customer')));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('orders.show', $order)->with('success', '¡Pedido enviado! El carnicero lo revisará pronto.');
    }
}
