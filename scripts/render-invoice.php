<?php
// Renderiza la factura del primer pedido a un PDF para verificación visual.
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$order = App\Models\Order::with(['items', 'customer.user'])->first();
if (! $order) {
    fwrite(STDERR, "No hay pedidos.\n");
    exit(1);
}

if (! $order->invoice_number) {
    $order->invoice_number = App\Support\Sequences::nextInvoiceNumber();
    $order->invoiced_at = now();
    $order->save();
}

$pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.show', [
    'order' => $order,
    'settings' => App\Models\BusinessSetting::current(),
])->setPaper('letter');

$out = __DIR__.'/../storage/invoice-test.pdf';
file_put_contents($out, $pdf->output());
echo 'wrote '.$order->invoice_number.' -> '.$out.PHP_EOL;
