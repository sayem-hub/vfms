<?php

namespace App\Filament\Resources\FixedRoutes\Pages;

use App\Filament\Resources\FixedRoutes\FixedRouteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFixedRoute extends EditRecord
{
    protected static string $resource = FixedRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
