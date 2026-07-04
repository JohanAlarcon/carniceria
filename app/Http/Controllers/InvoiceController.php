<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Order;
use App\Support\Sequences;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Request $request, Order $order)
    {
        abort_unless($request->user() && $request->user()->is_staff, 403);

        // Solo se imprime la factura de órdenes ya facturadas (el cambio de estado
        // ocurre desde la acción "Facturar" del panel, previa confirmación).
        abort_unless($order->status === 'facturado', 404);

        if (! $order->invoice_number) {
            $order->invoice_number = Sequences::nextInvoiceNumber();
            $order->invoiced_at = now();
            $order->save();
        }

        $order->load(['items', 'customer.user']);
        $settings = BusinessSetting::current();

        $pdf = Pdf::loadView('invoices.show', [
            'order' => $order,
            'settings' => $settings,
        ])->setPaper('letter');

        return $pdf->stream('factura-'.$order->invoice_number.'.pdf');
    }
}
