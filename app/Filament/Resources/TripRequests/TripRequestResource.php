<?php

namespace App\Filament\Resources\TripRequests;

use App\Filament\Resources\TripRequests\Pages\CreateTripRequest;
use App\Filament\Resources\TripRequests\Pages\EditTripRequest;
use App\Filament\Resources\TripRequests\Pages\ListTripRequests;
use App\Filament\Resources\TripRequests\Schemas\TripRequestForm;
use App\Filament\Resources\TripRequests\Tables\TripRequestsTable;
use App\Models\TripRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TripRequestResource extends Resource
{
    protected static ?string $model = TripRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'ট্রিপ ও রিকোয়েস্ট' : 'Trips & Operations';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট' : 'Trip Requests';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট' : 'Trip Request';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট সমূহ' : 'Trip Requests';
    }

    public static function form(Schema $schema): Schema
    {
        return TripRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TripRequestsTable::configure($table);
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
            'index' => ListTripRequests::route('/'),
            'create' => CreateTripRequest::route('/create'),
            'edit' => EditTripRequest::route('/{record}/edit'),
        ];
    }
}
