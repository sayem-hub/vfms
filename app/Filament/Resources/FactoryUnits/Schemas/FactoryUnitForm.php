<?php

namespace App\Filament\Resources\FactoryUnits\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FactoryUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('location_code')
                    ->required(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('contact_person_name'),
                TextInput::make('contact_person_phone')
                    ->tel(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
