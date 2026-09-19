<?php

namespace App\Filament\Resources\VehicleGateLogs\Pages;

use App\Filament\Resources\VehicleGateLogs\VehicleGateLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleGateLog extends EditRecord
{
    protected static string $resource = VehicleGateLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
