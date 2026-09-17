<?php

namespace App\Filament\Resources\TripExpenseSettlements\Pages;

use App\Filament\Resources\TripExpenseSettlements\TripExpenseSettlementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTripExpenseSettlements extends ListRecords
{
    protected static string $resource = TripExpenseSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
