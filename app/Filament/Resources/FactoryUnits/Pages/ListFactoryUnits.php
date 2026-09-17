<?php

namespace App\Filament\Resources\FactoryUnits\Pages;

use App\Filament\Resources\FactoryUnits\FactoryUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFactoryUnits extends ListRecords
{
    protected static string $resource = FactoryUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
