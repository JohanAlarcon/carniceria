<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
        'facturado' => 'Facturado',
        'cancelado' => 'Anulado',
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
                        // El estado se cambia con los botones Confirmar / Facturar / Anular,
                        // no a mano. Aquí solo se muestra como referencia.
                        Forms\Components\Placeholder::make('status_label')
                            ->label('Estado')
                            ->content(fn (?Order $record): string => $record?->statusLabel() ?? 'Pendiente'),
                        Forms\Components\DateTimePicker::make('requested_at')
                            ->label('Entrega (fecha y hora)')
                            ->seconds(false),
                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Notas internas')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Pago')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Forma de pago')
                            ->options(['contraentrega' => 'Contraentrega', 'credito' => 'Crédito'])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Estado de pago')
                            ->options(['pendiente' => 'Pendiente', 'pagado' => 'Pagado'])
                            ->required(),
                        Forms\Components\DatePicker::make('payment_due_date')
                            ->label('Fecha límite de pago')
                            ->visible(fn (Forms\Get $get) => $get('payment_method') === 'credito'),
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
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (Order $record): string => $record->statusLabel())
                    ->color(fn (Order $record): string => $record->statusColor())
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Renglones')
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge()
                    ->color(fn (string $state) => $state === 'credito' ? 'warning' : 'gray')
                    ->formatStateUsing(fn (string $state) => $state === 'credito' ? 'Crédito' : 'Contraentrega'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Estado pago')
                    ->badge()
                    ->color(fn (string $state) => $state === 'pagado' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'pagado' ? 'Pagado' : 'Pendiente'),
                Tables\Columns\TextColumn::make('requested_at')
                    ->label('Entrega')
                    ->dateTime('d/m/Y H:i')
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
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Forma de pago')
                    ->options(['contraentrega' => 'Contraentrega', 'credito' => 'Crédito']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->canBeConfirmed())
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar orden')
                    ->modalDescription('El cliente verá su pedido como confirmado.')
                    ->modalSubmitActionLabel('Confirmar')
                    ->action(function (Order $record) {
                        $record->markConfirmed();
                        Notification::make()->title('Orden confirmada')->success()->send();
                    }),

                Tables\Actions\Action::make('facturar')
                    ->label('Facturar')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->canBeInvoiced())
                    ->requiresConfirmation()
                    ->modalHeading('Facturar orden')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalDescription('La orden será facturada y ya no se podrá modificar. ¿Deseas continuar?')
                    ->modalSubmitActionLabel('Sí, facturar')
                    ->action(function (Order $record) {
                        $record->markInvoiced();

                        return redirect()->route('orders.invoice', $record);
                    }),

                Tables\Actions\Action::make('ver_factura')
                    ->label('Ver factura')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn (Order $record) => $record->isInvoiced())
                    ->url(fn (Order $record) => route('orders.invoice', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record) => $record->canBeCancelled())
                    ->requiresConfirmation()
                    ->modalHeading('Anular orden')
                    ->modalDescription('La orden quedará anulada y no se podrá modificar ni facturar.')
                    ->modalSubmitActionLabel('Sí, anular')
                    ->action(function (Order $record) {
                        $record->markCancelled();
                        Notification::make()->title('Orden anulada')->danger()->send();
                    }),

                Tables\Actions\Action::make('marcar_pagado')
                    ->label('Marcar pagado')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->payment_method === 'credito' && $record->payment_status !== 'pagado' && $record->status !== 'cancelado')
                    ->action(fn (Order $record) => $record->update(['payment_status' => 'pagado', 'paid_at' => now()])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Una orden facturada o anulada no se puede modificar.
    public static function canEdit(Model $record): bool
    {
        return ! $record->isLocked();
    }

    // Una orden facturada no se puede eliminar (queda como registro contable).
    public static function canDelete(Model $record): bool
    {
        return $record->status !== 'facturado';
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
