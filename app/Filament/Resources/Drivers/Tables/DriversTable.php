<?php

namespace App\Filament\Resources\Drivers\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DriversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label(fn () => __('vfms.driver_photo'))
                    ->circular(),
                TextColumn::make('office_id_card')
                    ->label(fn () => __('vfms.office_id_card'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(fn () => __('vfms.driver_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(fn () => __('vfms.phone'))
                    ->searchable(),
                TextColumn::make('factoryUnit.name')
                    ->label(fn () => __('vfms.factory_unit'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('license_number')
                    ->label(fn () => __('vfms.license_number'))
                    ->searchable(),
                TextColumn::make('license_expiry_date')
                    ->label(fn () => __('vfms.license_expiry'))
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : 'success'),
                TextColumn::make('currentVehicle.registration_no')
                    ->label(fn () => __('vfms.current_vehicle'))
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->label(fn () => __('vfms.employment_type'))
                    ->badge(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
