<?php

namespace App\Filament\Resources\FuelLogs;

use App\Filament\Resources\FuelLogs\Pages\CreateFuelLog;
use App\Filament\Resources\FuelLogs\Pages\EditFuelLog;
use App\Filament\Resources\FuelLogs\Pages\ListFuelLogs;
use App\Filament\Resources\FuelLogs\Schemas\FuelLogForm;
use App\Filament\Resources\FuelLogs\Tables\FuelLogsTable;
use App\Models\FuelLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FuelLogResource extends Resource
{
    protected static ?string $model = FuelLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'জ্বালানী ও হিসাব সমন্বয়' : 'Fuel & Settlements';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'জ্বালানী লগ' : 'Fuel Logs';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'জ্বালানী লগ' : 'Fuel Log';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'জ্বালানী লগ সমূহ' : 'Fuel Logs';
    }

    public static function form(Schema $schema): Schema
    {
        return FuelLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FuelLogsTable::configure($table);
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
            'index' => ListFuelLogs::route('/'),
            'create' => CreateFuelLog::route('/create'),
            'edit' => EditFuelLog::route('/{record}/edit'),
        ];
    }
}
