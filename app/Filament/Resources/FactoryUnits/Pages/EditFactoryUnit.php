<?php

namespace App\Filament\Resources\FactoryUnits\Pages;

use App\Filament\Resources\FactoryUnits\FactoryUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFactoryUnit extends EditRecord
{
    protected static string $resource = FactoryUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
