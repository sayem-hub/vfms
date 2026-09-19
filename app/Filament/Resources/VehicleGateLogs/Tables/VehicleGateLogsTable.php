<?php

namespace App\Filament\Resources\VehicleGateLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehicleGateLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('vehicle.registration_no')
                    ->label(fn () => __('vfms.registration_no'))
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('log_type')
                    ->label(fn () => __('vfms.usage_category'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DEDICATED_MANAGEMENT_CAR' => 'purple',
                        'STAFF_COMMUTE_BUS' => 'warning',
                        'MAINTENANCE_TEST_RUN' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'DEDICATED_MANAGEMENT_CAR' => '👔 Management Car',
                        'STAFF_COMMUTE_BUS' => '🚌 Staff Bus',
                        'MAINTENANCE_TEST_RUN' => '🔧 Test Run',
                        'OFFICIAL_DUTY' => '📋 Official Duty',
                        default => $state,
                    }),
                TextColumn::make('official_name')
                    ->label(fn () => __('vfms.dedicated_official'))
                    ->searchable()
                    ->placeholder('N/A'),
                TextColumn::make('fixedRoute.route_name')
                    ->label(fn () => __('vfms.fixed_route'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('driver.name')
                    ->label(fn () => __('vfms.driver'))
                    ->searchable(),
                TextColumn::make('gate_out_time')
                    ->label(fn () => __('vfms.gate_out'))
                    ->time('h:i A')
                    ->sortable(),
                TextColumn::make('gate_in_time')
                    ->label(fn () => __('vfms.gate_in'))
                    ->time('h:i A')
                    ->placeholder('--')
                    ->sortable(),
                TextColumn::make('out_odometer')
                    ->label(fn () => __('vfms.out_odometer'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('in_odometer')
                    ->label(fn () => __('vfms.in_odometer'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_km')
                    ->label(fn () => __('vfms.total_km_run'))
                    ->suffix(' KM')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(fn () => __('vfms.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'OUT' => 'warning',
                        'COMPLETED', 'IN' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('securityGuard.name')
                    ->label('Guard')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_type')
                    ->options([
                        'DEDICATED_MANAGEMENT_CAR' => 'Dedicated Management Car',
                        'STAFF_COMMUTE_BUS' => 'Staff Commute Bus',
                        'MAINTENANCE_TEST_RUN' => 'Maintenance Test Run',
                        'OFFICIAL_DUTY' => 'Official Duty',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'OUT' => 'OUT (Currently Out)',
                        'COMPLETED' => 'COMPLETED (Returned)',
                        'CANCELLED' => 'CANCELLED',
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
