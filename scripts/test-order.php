<?php
// Prueba de extremo a extremo del flujo de pedido (sin navegador).
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\OrderController;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$customer = Customer::query()->approved()->with('user')->first();
$user = $customer->user;
Auth::login($user);

$variants = ProductVariant::where('is_available', true)->take(3)->get();
$items = $variants->map(fn ($v) => ['variant_id' => $v->id, 'quantity' => 12])->all();

$before = Order::count();

$req = Request::create('/pedidos', 'POST', [
    'items' => $items,
    'delivery_address_line1' => '123 Test Ave',
    'delivery_city' => 'Queens',
    'delivery_state' => 'NY',
    'delivery_zip' => '11372',
    'delivery_notes' => 'prueba automatizada E2E',
]);
$req->setUserResolver(fn () => $user);
$session = $app->make('session')->driver();
$session->start();
$req->setLaravelSession($session);

try {
    $app->make(OrderController::class)->store($req);
} catch (\Throwable $e) {
    echo 'AVISO (post-commit): '.$e->getMessage().PHP_EOL;
}

$after = Order::count();
$order = Order::latest('id')->with('items')->first();
$firstItem = $order->items->first();
$v0 = $variants->first();
$expected = round((float) $v0->base_price * (1 + (float) $customer->price_adjustment_pct / 100), 2);

echo '--- Prueba de pedido ---'.PHP_EOL;
echo "cliente: {$customer->business_name} (ajuste {$customer->price_adjustment_pct}%)".PHP_EOL;
echo "pedidos: {$before} -> {$after}".PHP_EOL;
echo "folio: {$order->order_number}  renglones: ".$order->items->count()."  total: \${$order->total}".PHP_EOL;
echo "snapshot precio 1er renglón: base \${$v0->base_price} -> esperado \${$expected} / obtenido \${$firstItem->unit_price}".PHP_EOL;
echo 'RESULTADO: '.(($after === $before + 1 && (float) $firstItem->unit_price === $expected) ? 'OK ✅' : 'FALLO ❌').PHP_EOL;
