<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variantes (marca / grado / precio)';

    protected static ?string $modelLabel = 'variante';

    protected static ?string $pluralModelLabel = 'variantes';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('label_es')
                    ->label('Etiqueta (marca / grado)')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('brand')
                    ->label('Marca')
                    ->maxLength(255),
                Forms\Components\TextInput::make('grade')
                    ->label('Grado')
                    ->maxLength(255),
                Forms\Components\Select::make('unit')
                    ->label('Unidad')
                    ->options([
                        'lb' => 'Libra (lb)',
                        'caja' => 'Caja',
                        'pieza' => 'Pieza / paquete',
                        'hank' => 'Hank',
                    ])
                    ->default('lb')
                    ->required(),
                Forms\Components\TextInput::make('base_price')
                    ->label('Precio base')
                    ->prefix('$')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('unit_label_es')
                    ->label('Etiqueta unidad (ES)')
                    ->default('lb'),
                Forms\Components\TextInput::make('unit_label_en')
                    ->label('Etiqueta unidad (EN)')
                    ->default('lb'),
                Forms\Components\TextInput::make('package_weight_lb')
                    ->label('Peso de caja (lb)')
                    ->numeric(),
                Forms\Components\Toggle::make('is_frozen')
                    ->label('Congelado'),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponible')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label_es')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label_es')
                    ->label('Variante')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('grade')
                    ->label('Grado')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_frozen')
                    ->label('Congelado')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_label_es')
                    ->label('Unidad'),
                Tables\Columns\TextInputColumn::make('base_price')
                    ->label('Precio $')
                    ->type('number')
                    ->rules(['numeric', 'min:0'])
                    ->step(0.01),
                Tables\Columns\ToggleColumn::make('is_available')
                    ->label('Disponible'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva variante'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
