<?php

namespace App\Filament\Resources\VehicleCompliances\Pages;

use App\Filament\Resources\VehicleCompliances\VehicleComplianceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleCompliance extends EditRecord
{
    protected static string $resource = VehicleComplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
