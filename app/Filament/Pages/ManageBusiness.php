<?php

namespace App\Filament\Pages;

use App\Models\BusinessSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageBusiness extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del negocio';

    protected static string $view = 'filament.pages.manage-business';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(BusinessSetting::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Tabs::make()->tabs([
                    Forms\Components\Tabs\Tab::make('Negocio')
                        ->schema([
                            Forms\Components\FileUpload::make('logo_path')
                                ->label('Logo')
                                ->image()
                                ->disk('public')
                                ->directory('branding')
                                ->visibility('public')
                                ->maxSize(4096)
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                                ->helperText('Formatos: PNG, JPG, WEBP o SVG. Peso máximo 4 MB.')
                                ->validationMessages([
                                    'max' => 'El logo no debe superar los 4 MB.',
                                    'mimetypes' => 'Sube una imagen válida (PNG, JPG, WEBP o SVG).',
                                ]),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('business_name')->label('Nombre del negocio')->required(),
                                Forms\Components\TextInput::make('legal_name')->label('Razón social'),
                                Forms\Components\TextInput::make('phone')->label('Teléfono')->tel(),
                                Forms\Components\TextInput::make('email')->label('Email')->email(),
                                Forms\Components\TextInput::make('website')->label('Sitio web / redes'),
                                Forms\Components\TextInput::make('tax_id')->label('Tax ID / EIN'),
                            ]),
                        ]),
                    Forms\Components\Tabs\Tab::make('Dirección')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('address_line1')->label('Dirección'),
                                Forms\Components\TextInput::make('address_line2')->label('Dirección 2'),
                                Forms\Components\TextInput::make('city')->label('Ciudad'),
                                Forms\Components\TextInput::make('state')->label('Estado'),
                                Forms\Components\TextInput::make('zip')->label('ZIP'),
                                Forms\Components\TextInput::make('country')->label('País'),
                            ]),
                        ]),
                    Forms\Components\Tabs\Tab::make('Facturación')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('invoice_prefix')->label('Prefijo factura')->default('INV-'),
                                Forms\Components\TextInput::make('invoice_next_number')->label('Próximo # factura')->numeric()->minValue(1),
                                Forms\Components\TextInput::make('currency')->label('Moneda')->default('USD')->maxLength(3),
                            ]),
                            Forms\Components\Textarea::make('invoice_notes_es')->label('Notas (ES)')->rows(2),
                            Forms\Components\Textarea::make('invoice_notes_en')->label('Notas (EN)')->rows(2),
                            Forms\Components\Textarea::make('invoice_terms_es')->label('Términos (ES)')->rows(4),
                            Forms\Components\Textarea::make('invoice_terms_en')->label('Términos (EN)')->rows(4),
                        ]),
                    Forms\Components\Tabs\Tab::make('Pedidos y entrega')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Toggle::make('free_delivery')->label('Entrega gratis')->default(true),
                                Forms\Components\TextInput::make('default_shipping_fee')->label('Flete por defecto')->prefix('$')->numeric()->default(0),
                                Forms\Components\TextInput::make('min_order_amount')->label('Pedido mínimo')->prefix('$')->numeric(),
                                Forms\Components\TextInput::make('order_next_number')->label('Próximo # pedido')->numeric()->minValue(1),
                            ]),
                            Forms\Components\Fieldset::make('Ventana de entrega')
                                ->schema([
                                    Forms\Components\TimePicker::make('delivery_start_time')->label('Entrega desde')->seconds(false)->default('08:00'),
                                    Forms\Components\TimePicker::make('delivery_end_time')->label('Entrega hasta')->seconds(false)->default('18:00'),
                                    Forms\Components\TextInput::make('delivery_min_lead_days')
                                        ->label('Anticipación mínima')
                                        ->helperText('Cuánto antes debe hacerse el pedido. Ej.: 1 día = a partir de mañana; 4 horas = 4 h antes de la entrega.')
                                        ->numeric()->minValue(0)->default(1),
                                    Forms\Components\Select::make('delivery_min_lead_unit')
                                        ->label('Unidad de anticipación')
                                        ->options(['days' => 'Días', 'hours' => 'Horas'])
                                        ->default('days')
                                        ->selectablePlaceholder(false)
                                        ->native(false),
                                ]),
                            Forms\Components\Fieldset::make('Crédito')
                                ->schema([
                                    Forms\Components\TextInput::make('credit_terms_days')->label('Plazo de crédito por defecto (días)')->helperText('Se usa cuando el cliente no tiene un plazo propio.')->numeric()->minValue(1)->default(30),
                                ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        BusinessSetting::current()->update($this->form->getState());

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
