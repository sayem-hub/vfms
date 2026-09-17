<?php

namespace App\Filament\Resources\TripExpenseSettlements;

use App\Filament\Resources\TripExpenseSettlements\Pages\CreateTripExpenseSettlement;
use App\Filament\Resources\TripExpenseSettlements\Pages\EditTripExpenseSettlement;
use App\Filament\Resources\TripExpenseSettlements\Pages\ListTripExpenseSettlements;
use App\Filament\Resources\TripExpenseSettlements\Schemas\TripExpenseSettlementForm;
use App\Filament\Resources\TripExpenseSettlements\Tables\TripExpenseSettlementsTable;
use App\Models\TripExpenseSettlement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TripExpenseSettlementResource extends Resource
{
    protected static ?string $model = TripExpenseSettlement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'জ্বালানী ও হিসাব সমন্বয়' : 'Fuel & Settlements';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ভাউচার ও খরচ সমন্বয়' : 'Trip Settlements';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'খরচ সমন্বয়' : 'Trip Settlement';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ভাউচার ও খরচ সমন্বয় সমূহ' : 'Trip Settlements';
    }

    public static function form(Schema $schema): Schema
    {
        return TripExpenseSettlementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TripExpenseSettlementsTable::configure($table);
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
            'index' => ListTripExpenseSettlements::route('/'),
            'create' => CreateTripExpenseSettlement::route('/create'),
            'edit' => EditTripExpenseSettlement::route('/{record}/edit'),
        ];
    }
}
