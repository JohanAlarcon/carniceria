<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Support\Sequences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
                'requested_at' => optional($o->requested_at)->toIso8601String(),
                'payment_method' => $o->payment_method,
                'payment_status' => $o->payment_status,
                'payment_due_date' => optional($o->payment_due_date)->toDateString(),
            ])->values()
            : collect();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function checkout(Request $request): Response
    {
        $customer = $request->user()->customer;
        $settings = BusinessSetting::current();

        return Inertia::render('Checkout', [
            'credit' => [
                'enabled' => (bool) $customer?->credit_enabled,
                'available' => $customer ? round($customer->availableCredit(), 2) : 0,
                'terms_days' => $customer ? $customer->creditTermsDays() : (int) $settings->credit_terms_days,
            ],
            'delivery' => [
                'start_time' => substr((string) $settings->delivery_start_time, 0, 5),
                'end_time' => substr((string) $settings->delivery_end_time, 0, 5),
                'min_lead_days' => (int) $settings->delivery_min_lead_days,
            ],
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
                'requested_at' => optional($order->requested_at)->toIso8601String(),
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'payment_due_date' => optional($order->payment_due_date)->toDateString(),
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

        $settings = BusinessSetting::current();

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
            'requested_at' => 'required|date',
            'delivery_notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:contraentrega,credito',
        ]);

        $requestedAt = Carbon::parse($data['requested_at']);
        $this->validateDeliveryWindow($requestedAt, $settings);

        $isCredit = $data['payment_method'] === 'credito';
        if ($isCredit && ! $customer->credit_enabled) {
            throw ValidationException::withMessages([
                'payment_method' => 'Tu cuenta no tiene crédito habilitado.',
            ]);
        }

        $variants = ProductVariant::with('product')
            ->whereIn('id', collect($data['items'])->pluck('variant_id'))
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        abort_if($variants->isEmpty(), 422, 'No hay productos disponibles en el pedido.');

        $order = DB::transaction(function () use ($customer, $data, $variants, $requestedAt, $isCredit) {
            $termsDays = $isCredit ? $customer->creditTermsDays() : null;

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
                'requested_at' => $requestedAt,
                'requested_date' => $requestedAt->toDateString(),
                'delivery_notes' => $data['delivery_notes'] ?? null,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pendiente',
                'credit_terms_days' => $termsDays,
                'payment_due_date' => $isCredit ? $requestedAt->copy()->startOfDay()->addDays($termsDays)->toDateString() : null,
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

            // El pedido a crédito no puede superar el cupo disponible del cliente.
            if ($isCredit && $subtotal > $customer->availableCredit()) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Este pedido ($'.number_format($subtotal, 2).') supera tu cupo de crédito disponible ($'.number_format($customer->availableCredit(), 2).').',
                ]);
            }

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            // Guarda la dirección usada como la última dirección de entrega del cliente
            // (refleja exactamente lo enviado, igual que el snapshot del pedido).
            $customer->update([
                'address_line1' => $data['delivery_address_line1'],
                'address_line2' => $data['delivery_address_line2'] ?? null,
                'city' => $data['delivery_city'],
                'state' => $data['delivery_state'] ?? null,
                'zip' => $data['delivery_zip'] ?? null,
            ]);

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

    /** La entrega debe caer dentro del horario y respetar la anticipación mínima. */
    private function validateDeliveryWindow(Carbon $requestedAt, BusinessSetting $settings): void
    {
        $minDate = now()->startOfDay()->addDays((int) $settings->delivery_min_lead_days);
        if ($requestedAt->copy()->startOfDay()->lt($minDate)) {
            throw ValidationException::withMessages([
                'requested_at' => 'La fecha de entrega debe ser a partir del '.$minDate->format('d/m/Y').'.',
            ]);
        }

        $start = substr((string) $settings->delivery_start_time, 0, 5);
        $end = substr((string) $settings->delivery_end_time, 0, 5);
        $time = $requestedAt->format('H:i');
        if ($time < $start || $time > $end) {
            throw ValidationException::withMessages([
                'requested_at' => "La hora de entrega debe estar entre $start y $end.",
            ]);
        }
    }
}
