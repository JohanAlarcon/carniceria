<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exporta los reportes de ventas a PDF (dompdf) y Excel (OpenSpout .xlsx).
 * Reutiliza SalesReportService para que los números sean idénticos a la pantalla.
 */
class SalesReportExporter
{
    public function __construct(private SalesReportService $reports) {}

    /** Reúne todos los datos del reporte para un conjunto de filtros. */
    public function data(array $filters): array
    {
        $range = $this->reports->resolveRange($filters);

        return [
            'range' => $range,
            'summary' => $this->reports->summary($range['start'], $range['end']),
            'topProducts' => $this->reports->topProducts($range['start'], $range['end'], 100),
            'byCustomer' => $this->reports->salesByCustomer($range['start'], $range['end']),
            'series' => $this->reports->salesOverTime($range['start'], $range['end']),
            'business' => BusinessSetting::current(),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];
    }

    public function pdf(array $filters): Response
    {
        $data = $this->data($filters);

        return Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4')
            ->download($this->filename($data['range']['label'], 'pdf'));
    }

    public function excel(array $filters): Response
    {
        $data = $this->data($filters);
        $path = tempnam(sys_get_temp_dir(), 'rep').'.xlsx';

        $writer = new Writer();
        $writer->openToFile($path);

        // Hoja 1: Resumen
        $writer->getCurrentSheet()->setName('Resumen');
        $writer->addRow(Row::fromValues(['Reporte de ventas']));
        $writer->addRow(Row::fromValues(['Período', $data['range']['label']]));
        $writer->addRow(Row::fromValues(['Generado', $data['generatedAt']]));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Métrica', 'Valor']));
        $writer->addRow(Row::fromValues(['Ingresos', $data['summary']['revenue']]));
        $writer->addRow(Row::fromValues(['Pedidos', $data['summary']['orders']]));
        $writer->addRow(Row::fromValues(['Ticket promedio', $data['summary']['avg_ticket']]));
        $writer->addRow(Row::fromValues(['Unidades vendidas', $data['summary']['units']]));

        // Hoja 2: Productos más vendidos
        $writer->addNewSheetAndMakeItCurrent()->setName('Productos');
        $writer->addRow(Row::fromValues(['#', 'Producto', 'Ingresos', 'Cantidad']));
        foreach ($data['topProducts'] as $i => $p) {
            $writer->addRow(Row::fromValues([$i + 1, $p['product_name'], $p['revenue'], $p['quantity']]));
        }

        // Hoja 3: Ventas por cliente
        $writer->addNewSheetAndMakeItCurrent()->setName('Clientes');
        $writer->addRow(Row::fromValues(['Cliente', 'Ingresos', 'Pedidos', 'Ticket promedio']));
        foreach ($data['byCustomer'] as $c) {
            $writer->addRow(Row::fromValues([$c['business_name'], $c['revenue'], $c['orders'], $c['avg_ticket']]));
        }

        // Hoja 4: Ventas en el tiempo
        $writer->addNewSheetAndMakeItCurrent()->setName('Ventas en el tiempo');
        $writer->addRow(Row::fromValues(['Período', 'Ventas']));
        foreach ($data['series']['labels'] as $i => $label) {
            $writer->addRow(Row::fromValues([$label, $data['series']['data'][$i] ?? 0]));
        }

        $writer->close();

        return response()->download($path, $this->filename($data['range']['label'], 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    private function filename(string $label, string $ext): string
    {
        return 'reporte-ventas-'.Str::slug($label).'.'.$ext;
    }
}
