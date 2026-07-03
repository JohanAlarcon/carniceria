<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use App\Support\Icons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'producto';

    protected static ?string $pluralModelLabel = 'productos';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Producto')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name_es')
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('icon')
                            ->label('Icono')
                            ->options(Icons::options())
                            ->searchable(),
                        Forms\Components\TextInput::make('name_es')
                            ->label('Nombre (ES)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        Forms\Components\TextInput::make('name_en')
                            ->label('Nombre (EN)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('english_cut')
                            ->label('Corte en inglés')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description_es')
                            ->label('Descripción (ES)')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description_en')
                            ->label('Descripción (EN)')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Presentación')
                    ->columns(3)
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Foto real (opcional)')
                            ->image()
                            ->imageEditor()
                            ->directory('products')
                            ->visibility('public'),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publicado en la tienda')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('')
                    ->getStateUsing(fn (Product $record) => $record->icon ? asset($record->icon) : null)
                    ->size(40),
                Tables\Columns\TextColumn::make('name_es')
                    ->label('Producto')
                    ->description(fn (Product $record) => $record->name_en)
                    ->searchable(['name_es', 'name_en'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name_es')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('variants_count')
                    ->label('Variantes')
                    ->counts('variants')
                    ->badge(),
                Tables\Columns\TextColumn::make('lowest_price')
                    ->label('Desde')
                    ->money('USD')
                    ->getStateUsing(fn (Product $record) => $record->lowestPrice()),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name_es')
                    ->label('Categoría'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publicado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
