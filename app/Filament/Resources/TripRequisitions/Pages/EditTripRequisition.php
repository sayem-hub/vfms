<?php

namespace App\Filament\Resources\TripRequisitions\Pages;

use App\Filament\Resources\TripRequisitions\TripRequisitionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTripRequisition extends EditRecord
{
    protected static string $resource = TripRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
