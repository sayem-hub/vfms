<?php

namespace App\Filament\Resources\ScrapPartsSurrenders\Pages;

use App\Filament\Resources\ScrapPartsSurrenders\ScrapPartsSurrenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScrapPartsSurrenders extends ListRecords
{
    protected static string $resource = ScrapPartsSurrenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
