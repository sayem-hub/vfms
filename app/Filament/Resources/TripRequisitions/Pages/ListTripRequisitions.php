<?php

namespace App\Filament\Resources\TripRequisitions\Pages;

use App\Filament\Resources\TripRequisitions\TripRequisitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTripRequisitions extends ListRecords
{
    protected static string $resource = TripRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
