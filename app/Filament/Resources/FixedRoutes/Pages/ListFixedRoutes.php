<?php

namespace App\Filament\Resources\FixedRoutes\Pages;

use App\Filament\Resources\FixedRoutes\FixedRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFixedRoutes extends ListRecords
{
    protected static string $resource = FixedRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
