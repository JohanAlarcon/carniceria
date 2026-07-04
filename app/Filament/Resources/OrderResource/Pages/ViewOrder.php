<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirmar')
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

            Actions\Action::make('facturar')
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

            Actions\Action::make('ver_factura')
                ->label('Ver factura')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->visible(fn (Order $record) => $record->isInvoiced())
                ->url(fn (Order $record) => route('orders.invoice', $record))
                ->openUrlInNewTab(),

            Actions\Action::make('anular')
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

            Actions\EditAction::make(),
        ];
    }
}
