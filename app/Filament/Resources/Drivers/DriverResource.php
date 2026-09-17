<?php

namespace App\Filament\Resources\Drivers;

use App\Filament\Resources\Drivers\Pages\CreateDriver;
use App\Filament\Resources\Drivers\Pages\EditDriver;
use App\Filament\Resources\Drivers\Pages\ListDrivers;
use App\Filament\Resources\Drivers\Schemas\DriverForm;
use App\Filament\Resources\Drivers\Tables\DriversTable;
use App\Models\Driver;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'যানবাহন ও চালক' : 'Fleet & Drivers';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'চালক / ড্রাইভারবৃন্দ' : 'Drivers';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ড্রাইভার' : 'Driver';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ড্রাইভারবৃন্দ' : 'Drivers';
    }

    public static function form(Schema $schema): Schema
    {
        return DriverForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriversTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrivers::route('/'),
            'create' => CreateDriver::route('/create'),
            'edit' => EditDriver::route('/{record}/edit'),
        ];
    }
}
