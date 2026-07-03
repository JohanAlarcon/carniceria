<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Renglones del pedido';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Producto')
                    ->description(fn ($record) => $record->variant_label)
                    ->wrap(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2).' '.$record->unit_label),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Precio')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Importe')
                    ->money('USD'),
            ]);
    }
}
