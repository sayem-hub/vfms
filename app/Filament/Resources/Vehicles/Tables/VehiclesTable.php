<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('factoryUnit.name')
                    ->searchable(),
                TextColumn::make('registration_no')
                    ->searchable(),
                TextColumn::make('vehicle_type')
                    ->searchable(),
                TextColumn::make('ownership_type')
                    ->searchable(),
                TextColumn::make('fuel_type')
                    ->searchable(),
                TextColumn::make('fuel_payer')
                    ->searchable(),
                TextColumn::make('maintenance_payer')
                    ->searchable(),
                TextColumn::make('driver_payer')
                    ->searchable(),
                TextColumn::make('monthly_fuel_quota_liters')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('monthly_fixed_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('rate_per_km')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_odometer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fuel_capacity_liters')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expected_km_per_liter')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('model_name')
                    ->searchable(),
                TextColumn::make('model_year')
                    ->searchable(),
                TextColumn::make('chassis_number')
                    ->searchable(),
                TextColumn::make('engine_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
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
