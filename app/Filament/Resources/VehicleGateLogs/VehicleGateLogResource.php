<?php

namespace App\Filament\Resources\VehicleGateLogs;

use App\Filament\Resources\VehicleGateLogs\Pages\CreateVehicleGateLog;
use App\Filament\Resources\VehicleGateLogs\Pages\EditVehicleGateLog;
use App\Filament\Resources\VehicleGateLogs\Pages\ListVehicleGateLogs;
use App\Filament\Resources\VehicleGateLogs\Schemas\VehicleGateLogForm;
use App\Filament\Resources\VehicleGateLogs\Tables\VehicleGateLogsTable;
use App\Models\VehicleGateLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleGateLogResource extends Resource
{
    protected static ?string $model = VehicleGateLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'যানবাহন ও চালক' : 'Fleet & Drivers';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'দৈনিক ডিজিটাল লগবই' : 'Daily Vehicle Logbook';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'লগ এন্ট্রি' : 'Gate Log Entry';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'দৈনিক ডিজিটাল লগবই' : 'Daily Vehicle Logbook';
    }

    public static function form(Schema $schema): Schema
    {
        return VehicleGateLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleGateLogsTable::configure($table);
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
            'index' => ListVehicleGateLogs::route('/'),
            'create' => CreateVehicleGateLog::route('/create'),
            'edit' => EditVehicleGateLog::route('/{record}/edit'),
        ];
    }
}
