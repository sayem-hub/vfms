<?php

namespace App\Filament\Resources\TripRequests\Pages;

use App\Filament\Resources\TripRequests\TripRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTripRequests extends ListRecords
{
    protected static string $resource = TripRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
