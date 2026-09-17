<?php

namespace App\Filament\Resources\ScrapPartsSurrenders;

use App\Filament\Resources\ScrapPartsSurrenders\Pages\CreateScrapPartsSurrender;
use App\Filament\Resources\ScrapPartsSurrenders\Pages\EditScrapPartsSurrender;
use App\Filament\Resources\ScrapPartsSurrenders\Pages\ListScrapPartsSurrenders;
use App\Filament\Resources\ScrapPartsSurrenders\Schemas\ScrapPartsSurrenderForm;
use App\Filament\Resources\ScrapPartsSurrenders\Tables\ScrapPartsSurrendersTable;
use App\Models\ScrapPartsSurrender;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScrapPartsSurrenderResource extends Resource
{
    protected static ?string $model = ScrapPartsSurrender::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'মেরামত ও স্টোর' : 'Maintenance & Store';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'পুরাতন পার্টস স্টোর জমা' : 'Scrap Parts Surrenders';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'পুরাতন পার্টস জমা' : 'Scrap Part Surrender';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'পুরাতন পার্টস জমা সমূহ' : 'Scrap Parts Surrenders';
    }

    public static function form(Schema $schema): Schema
    {
        return ScrapPartsSurrenderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScrapPartsSurrendersTable::configure($table);
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
            'index' => ListScrapPartsSurrenders::route('/'),
            'create' => CreateScrapPartsSurrender::route('/create'),
            'edit' => EditScrapPartsSurrender::route('/{record}/edit'),
        ];
    }
}
