<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Pedidos';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'pedido';

    protected static ?string $pluralModelLabel = 'pedidos';

    protected static ?int $navigationSort = 1;

    public static array $statusOptions = [
        'pendiente' => 'Pendiente',
        'confirmado' => 'Confirmado',
        'en_preparacion' => 'En preparación',
        'en_ruta' => 'En ruta',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pendiente')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Estado del pedido')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(self::$statusOptions)
                            ->required(),
                        Forms\Components\DatePicker::make('requested_date')
                            ->label('Fecha solicitada'),
                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Notas internas')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Entrega')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('delivery_business_name')->label('Negocio'),
                        Forms\Components\TextInput::make('delivery_contact_name')->label('Contacto'),
                        Forms\Components\TextInput::make('delivery_phone')->label('Teléfono')->tel(),
                        Forms\Components\TextInput::make('delivery_address_line1')->label('Dirección'),
                        Forms\Components\TextInput::make('delivery_address_line2')->label('Dirección 2'),
                        Forms\Components\TextInput::make('delivery_city')->label('Ciudad'),
                        Forms\Components\TextInput::make('delivery_state')->label('Estado'),
                        Forms\Components\TextInput::make('delivery_zip')->label('ZIP'),
                        Forms\Components\Textarea::make('delivery_notes')->label('Notas de entrega')->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Totales')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('price_adjustment_pct')->label('Ajuste %')->disabled(),
                        Forms\Components\TextInput::make('subtotal')->label('Subtotal')->prefix('$')->disabled(),
                        Forms\Components\TextInput::make('total')->label('Total')->prefix('$')->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('15s')
            ->defaultSort('placed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Pedido')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.business_name')
                    ->label('Cliente')
                    ->description(fn (Order $record) => $record->customer?->user?->email)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Estado')
                    ->options(self::$statusOptions)
                    ->selectablePlaceholder(false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Renglones')
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('requested_date')
                    ->label('Entrega')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('placed_at')
                    ->label('Recibido')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(self::$statusOptions),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('invoice')
                    ->label('Factura')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn (Order $record) => route('orders.invoice', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
