<?php

namespace App\Filament\Resources\TripExpenseSettlements\Pages;

use App\Filament\Resources\TripExpenseSettlements\TripExpenseSettlementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTripExpenseSettlement extends EditRecord
{
    protected static string $resource = TripExpenseSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
