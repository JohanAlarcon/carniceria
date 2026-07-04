<?php

namespace App\Filament\Pages;

use App\Filament\Reports\Widgets\ReportStats;
use App\Filament\Reports\Widgets\SalesByCustomer;
use App\Filament\Reports\Widgets\SalesOverTimeChart;
use App\Filament\Reports\Widgets\TopProducts;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Reportes extends BaseDashboard
{
    use HasFiltersForm;

    // Ruta propia: sin esto chocaría con el Escritorio (que vive en '/').
    protected static string $routePath = '/reportes';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes de ventas';

    protected static ?int $navigationSort = 1;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('preset')
                    ->label('Período')
                    ->options([
                        'hoy' => 'Hoy',
                        'semana_pasada' => 'Semana pasada',
                        'mes_pasado' => 'Mes pasado',
                        'anio' => 'Año en curso',
                        'personalizado' => 'Personalizado',
                    ])
                    ->default('anio')
                    ->selectablePlaceholder(false)
                    ->live(),

                DatePicker::make('desde')
                    ->label('Desde')
                    ->native(false)
                    ->visible(fn (Get $get): bool => $get('preset') === 'personalizado'),

                DatePicker::make('hasta')
                    ->label('Hasta')
                    ->native(false)
                    ->visible(fn (Get $get): bool => $get('preset') === 'personalizado'),
            ]);
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            ReportStats::class,
            SalesOverTimeChart::class,
            TopProducts::class,
            SalesByCustomer::class,
        ];
    }
}
