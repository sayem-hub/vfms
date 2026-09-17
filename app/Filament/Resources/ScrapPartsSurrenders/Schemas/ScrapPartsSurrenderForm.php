<?php

namespace App\Filament\Resources\ScrapPartsSurrenders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScrapPartsSurrenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('maintenance_record_id')
                    ->relationship('maintenanceRecord', 'id')
                    ->required(),
                Select::make('factory_unit_id')
                    ->relationship('factoryUnit', 'name')
                    ->required(),
                TextInput::make('item_name')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('item_serial_or_code'),
                TextInput::make('photo_of_scrap_part'),
                Select::make('received_by_store_officer_id')
                    ->relationship('receivedByStoreOfficer', 'name')
                    ->required(),
                TextInput::make('scrap_bin_location'),
            ]);
    }
}
