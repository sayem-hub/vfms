<?php

namespace App\Filament\Resources\FuelLogs\Tables;

use App\Services\Audit\FuelEfficiencyService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FuelLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.registration_no')
                    ->label(fn () => __('vfms.registration_no'))
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('driver.name')
                    ->label(fn () => __('vfms.driver_name'))
                    ->searchable(),
                TextColumn::make('station_name')
                    ->label(fn () => __('vfms.filling_station'))
                    ->searchable(),
                TextColumn::make('odometer_reading')
                    ->label(fn () => __('vfms.current_odometer'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fuel_quantity')
                    ->label(fn () => __('vfms.fuel_quantity'))
                    ->suffix(' L/m³')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label(fn () => __('vfms.total_fuel_cost'))
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('calculated_km_per_liter')
                    ->label(fn () => __('vfms.calculated_km_per_liter'))
                    ->suffix(' KM/L')
                    ->badge()
                    ->sortable(),
                TextColumn::make('is_efficiency_anomaly')
                    ->label('Siphon Anomaly')
                    ->badge()
                    ->state(fn ($record) => $record->is_efficiency_anomaly ? '⚠️ অপচয় / চুরি (Drop >20%)' : 'স্বাভাবিক (Normal)')
                    ->color(fn ($record) => $record->is_efficiency_anomaly ? 'danger' : 'success'),
                ImageColumn::make('dispenser_photo')
                    ->label('Dispenser Photo')
                    ->circular(),
                ImageColumn::make('odometer_photo')
                    ->label('Odometer Photo')
                    ->circular(),
                ImageColumn::make('receipt_memo_photo')
                    ->label('Receipt Photo')
                    ->circular(),
                TextColumn::make('refill_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_efficiency_anomaly')
                    ->label('Efficiency Anomaly')
                    ->options([
                        '1' => 'Efficiency Drop Alert (>20%)',
                        '0' => 'Normal Burn Rate',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'PETTY_CASH_ADVANCE' => 'Petty Cash Advance',
                        'DRIVER_POCKET_REIMBURSABLE' => 'Reimbursable from Driver Pocket',
                        'COMPANY_CREDIT_VOUCHER' => 'Company Credit Voucher',
                    ]),
            ])
            ->recordActions([
                Action::make('audit_efficiency')
                    ->label('Audit Fuel (ফুয়েল অডিট)')
                    ->icon(Heroicon::OutlinedCalculator)
                    ->color('warning')
                    ->action(function ($record, FuelEfficiencyService $efficiencyService) {
                        $efficiencyService->auditFuelLog($record);
                        Notification::make()
                            ->title('Fuel Efficiency Audited')
                            ->body($record->is_efficiency_anomaly
                                ? "⚠️ Efficiency drop alert! Calculated: {$record->calculated_km_per_liter} KM/L"
                                : "✅ Normal burn rate verified: {$record->calculated_km_per_liter} KM/L")
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
