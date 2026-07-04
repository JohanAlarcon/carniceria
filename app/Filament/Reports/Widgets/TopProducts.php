<?php

namespace App\Filament\Reports\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class TopProducts extends Widget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.reports.top-products';

    protected int|string|array $columnSpan = 1;

    /** @return array<int, array{product_name: string, revenue: float, quantity: float}> */
    public function getRows(): array
    {
        $service = app(SalesReportService::class);
        $range = $service->resolveRange($this->filters ?? []);

        return $service->topProducts($range['start'], $range['end'], 10);
    }
}
