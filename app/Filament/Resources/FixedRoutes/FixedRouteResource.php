<?php

namespace App\Filament\Resources\FixedRoutes;

use App\Filament\Resources\FixedRoutes\Pages\CreateFixedRoute;
use App\Filament\Resources\FixedRoutes\Pages\EditFixedRoute;
use App\Filament\Resources\FixedRoutes\Pages\ListFixedRoutes;
use App\Filament\Resources\FixedRoutes\Schemas\FixedRouteForm;
use App\Filament\Resources\FixedRoutes\Tables\FixedRoutesTable;
use App\Models\FixedRoute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FixedRouteResource extends Resource
{
    protected static ?string $model = FixedRoute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'যানবাহন ও চালক' : 'Fleet & Drivers';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'স্থায়ী ও স্টাফ রুট' : 'Fixed Routes & Commute';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'স্থায়ী রুট' : 'Fixed Route';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'স্থায়ী রুট সমূহ' : 'Fixed Routes';
    }

    public static function form(Schema $schema): Schema
    {
        return FixedRouteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FixedRoutesTable::configure($table);
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
            'index' => ListFixedRoutes::route('/'),
            'create' => CreateFixedRoute::route('/create'),
            'edit' => EditFixedRoute::route('/{record}/edit'),
        ];
    }
}
