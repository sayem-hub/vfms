<?php

namespace App\Filament\Resources\VehicleGateLogs\Pages;

use App\Filament\Resources\VehicleGateLogs\VehicleGateLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleGateLogs extends ListRecords
{
    protected static string $resource = VehicleGateLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
