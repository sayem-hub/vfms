<?php

namespace App\Filament\Resources\FixedRoutes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FixedRoutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('route_code')
                    ->label(fn () => __('vfms.route_code'))
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('route_name')
                    ->label(fn () => __('vfms.route_name'))
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('origin_name')
                    ->label(fn () => __('vfms.origin'))
                    ->searchable(),
                TextColumn::make('destination_name')
                    ->label(fn () => __('vfms.destination'))
                    ->searchable(),
                TextColumn::make('standard_distance_km')
                    ->label(fn () => __('vfms.expected_distance'))
                    ->suffix(' KM')
                    ->sortable(),
                TextColumn::make('scheduled_departure_time')
                    ->label(fn () => __('vfms.scheduled_departure')),
                TextColumn::make('assignedVehicle.registration_no')
                    ->label(fn () => __('vfms.assigned_vehicle'))
                    ->badge()
                    ->color('warning')
                    ->searchable(),
                TextColumn::make('assignedDriver.name')
                    ->label(fn () => __('vfms.assigned_driver'))
                    ->searchable(),
                TextColumn::make('shift_name')
                    ->label(fn () => __('vfms.shift_name'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(fn () => __('vfms.status'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
