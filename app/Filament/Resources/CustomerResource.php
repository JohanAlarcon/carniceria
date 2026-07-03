<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Clientes';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'cliente';

    protected static ?string $pluralModelLabel = 'clientes';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_approved', false)->count();

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
                Forms\Components\Section::make('Cuenta')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario (email)')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Forms\Components\Section::make('Negocio')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('business_name')
                            ->label('Nombre del negocio')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Contacto')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_line1')
                            ->label('Dirección')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_line2')
                            ->label('Dirección 2')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->label('Estado')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zip')
                            ->label('ZIP')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Precios y estado')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('price_adjustment_pct')
                            ->label('Ajuste de precio')
                            ->helperText('Sobre el precio base. Negativo = descuento (ej. -10), positivo = recargo (ej. 5).')
                            ->suffix('%')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Aprobado')
                            ->helperText('El cliente solo ve precios y puede pedir cuando está aprobado.'),
                        Forms\Components\Toggle::make('tax_exempt')
                            ->label('Exento de impuesto (resale)')
                            ->default(true),
                        Forms\Components\TextInput::make('resale_certificate')
                            ->label('Resale certificate')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas internas')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Negocio')
                    ->description(fn (Customer $record) => $record->user?->email)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contacto')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->formatStateUsing(fn (Customer $record) => trim(collect([$record->city, $record->state])->filter()->implode(', ')))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price_adjustment_pct')
                    ->label('Ajuste')
                    ->badge()
                    ->color(fn ($state) => $state < 0 ? 'success' : ($state > 0 ? 'warning' : 'gray'))
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '').number_format((float) $state, 2).'%'),
                Tables\Columns\IconColumn::make('tax_exempt')
                    ->label('Exento')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('Aprobado'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Aprobado'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('aprobar')
                        ->label('Aprobar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_approved' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
