<?php

namespace App\Filament\Resources\FactoryUnits;

use App\Filament\Resources\FactoryUnits\Pages\CreateFactoryUnit;
use App\Filament\Resources\FactoryUnits\Pages\EditFactoryUnit;
use App\Filament\Resources\FactoryUnits\Pages\ListFactoryUnits;
use App\Filament\Resources\FactoryUnits\Schemas\FactoryUnitForm;
use App\Filament\Resources\FactoryUnits\Tables\FactoryUnitsTable;
use App\Models\FactoryUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FactoryUnitResource extends Resource
{
    protected static ?string $model = FactoryUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'সংগঠন ও সেটিংস' : 'Organization & Settings';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ফ্যাক্টরি ইউনিট সমূহ' : 'Factory Units';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ফ্যাক্টরি ইউনিট' : 'Factory Unit';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ফ্যাক্টরি ইউনিট সমূহ' : 'Factory Units';
    }

    public static function form(Schema $schema): Schema
    {
        return FactoryUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FactoryUnitsTable::configure($table);
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
            'index' => ListFactoryUnits::route('/'),
            'create' => CreateFactoryUnit::route('/create'),
            'edit' => EditFactoryUnit::route('/{record}/edit'),
        ];
    }
}
