<?php

namespace App\Filament\Resources\VehicleCompliances\Pages;

use App\Filament\Resources\VehicleCompliances\VehicleComplianceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleCompliances extends ListRecords
{
    protected static string $resource = VehicleComplianceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
