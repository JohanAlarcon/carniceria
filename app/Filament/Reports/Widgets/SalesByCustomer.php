<?php

namespace App\Filament\Reports\Widgets;

use App\Services\SalesReportService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class SalesByCustomer extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.reports.sales-by-customer';

    protected int|string|array $columnSpan = 1;

    /** @return array<int, array{customer_id: int, business_name: string, revenue: float, orders: int, avg_ticket: float}> */
    public function getRows(): array
    {
        $service = app(SalesReportService::class);
        $range = $service->resolveRange($this->filters ?? []);

        return $service->salesByCustomer($range['start'], $range['end']);
    }

    /** Acción de fila: abre un panel lateral con los productos que compró el cliente. */
    public function detalleAction(): Action
    {
        return Action::make('detalle')
            ->label('Ver productos')
            ->icon('heroicon-m-eye')
            ->color('gray')
            ->link()
            ->slideOver()
            ->modalHeading('Productos comprados')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->modalContent(function (array $arguments) {
                $service = app(SalesReportService::class);
                $range = $service->resolveRange($this->filters ?? []);
                $rows = $service->productsForCustomer((int) $arguments['customer_id'], $range['start'], $range['end']);

                return view('filament.reports.customer-products', ['rows' => $rows]);
            });
    }
}
