<?php

namespace App\Filament\Resources\ScrapPartsSurrenders\Pages;

use App\Filament\Resources\ScrapPartsSurrenders\ScrapPartsSurrenderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScrapPartsSurrender extends EditRecord
{
    protected static string $resource = ScrapPartsSurrenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
