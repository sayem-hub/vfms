<?php

namespace App\Filament\Resources\TripRequisitions;

use App\Filament\Resources\TripRequisitions\Pages\CreateTripRequisition;
use App\Filament\Resources\TripRequisitions\Pages\EditTripRequisition;
use App\Filament\Resources\TripRequisitions\Pages\ListTripRequisitions;
use App\Filament\Resources\TripRequisitions\Schemas\TripRequisitionForm;
use App\Filament\Resources\TripRequisitions\Tables\TripRequisitionsTable;
use App\Models\TripRequisition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TripRequisitionResource extends Resource
{
    protected static ?string $model = TripRequisition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'ট্রিপ ও রিকুইজিশন' : 'Trips & Operations';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ট্রিপ রিকুইজিশন' : 'Trip Requisitions';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'রিকুইজিশন' : 'Requisition';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ট্রিপ রিকুইজিশন সমূহ' : 'Trip Requisitions';
    }

    public static function form(Schema $schema): Schema
    {
        return TripRequisitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TripRequisitionsTable::configure($table);
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
            'index' => ListTripRequisitions::route('/'),
            'create' => CreateTripRequisition::route('/create'),
            'edit' => EditTripRequisition::route('/{record}/edit'),
        ];
    }
}
