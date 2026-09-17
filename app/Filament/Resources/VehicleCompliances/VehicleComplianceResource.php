<?php

namespace App\Filament\Resources\VehicleCompliances;

use App\Filament\Resources\VehicleCompliances\Pages\CreateVehicleCompliance;
use App\Filament\Resources\VehicleCompliances\Pages\EditVehicleCompliance;
use App\Filament\Resources\VehicleCompliances\Pages\ListVehicleCompliances;
use App\Filament\Resources\VehicleCompliances\Schemas\VehicleComplianceForm;
use App\Filament\Resources\VehicleCompliances\Tables\VehicleCompliancesTable;
use App\Models\VehicleCompliance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleComplianceResource extends Resource
{
    protected static ?string $model = VehicleCompliance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'যানবাহন ও চালক' : 'Fleet & Drivers';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'বিআরটিএ নথিপত্র মেয়াদ' : 'BRTA Compliances';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'নথিপত্র মেয়াদ' : 'Compliance Document';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'বিআরটিএ নথিপত্র মেয়াদ' : 'BRTA Compliances';
    }

    public static function form(Schema $schema): Schema
    {
        return VehicleComplianceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleCompliancesTable::configure($table);
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
            'index' => ListVehicleCompliances::route('/'),
            'create' => CreateVehicleCompliance::route('/create'),
            'edit' => EditVehicleCompliance::route('/{record}/edit'),
        ];
    }
}
