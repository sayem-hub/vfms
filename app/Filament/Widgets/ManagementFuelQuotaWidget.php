<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ManagementFuelQuotaWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return app()->getLocale() === 'bn' ? 'ম্যানেজমেন্ট ফুয়েল কোটা ট্র্যাকার (Executive Fuel Quota Status)' : 'Executive Management Fuel Quota Status';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->where('is_active', true)
                    ->whereNotNull('monthly_fuel_quota_liters')
                    ->where('monthly_fuel_quota_liters', '>', 0)
            )
            ->columns([
                TextColumn::make('registration_no')
                    ->label(fn () => __('vfms.registration_no'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('dedicated_to_official')
                    ->label(fn () => __('vfms.dedicated_official'))
                    ->weight('bold'),
                TextColumn::make('monthly_fuel_quota_liters')
                    ->label(fn () => __('vfms.monthly_quota'))
                    ->suffix(' L')
                    ->numeric(),
                TextColumn::make('consumed')
                    ->label(fn () => __('vfms.consumed_fuel'))
                    ->state(fn ($record) => number_format($record->monthlyFuelConsumedLiters(), 1).' L')
                    ->color('warning'),
                TextColumn::make('remaining')
                    ->label(fn () => __('vfms.remaining_quota'))
                    ->state(fn ($record) => number_format($record->monthlyFuelQuotaRemaining(), 1).' L')
                    ->color(fn ($record) => $record->monthlyFuelQuotaRemaining() < 0 ? 'danger' : 'success')
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label(fn () => __('vfms.status'))
                    ->badge()
                    ->state(function ($record) {
                        $p = $record->monthlyFuelQuotaUsagePercent();
                        if ($p > 100) {
                            return app()->getLocale() === 'bn' ? '⚠️ কোটা অতিক্রান্ত (>100%)' : '⚠️ Quota Exceeded';
                        }
                        if ($p >= 80) {
                            return app()->getLocale() === 'bn' ? '⚡ সতর্কতা (≥80%)' : '⚡ 80% Used';
                        }

                        return app()->getLocale() === 'bn' ? '✓ স্বাভাবিক (Normal)' : '✓ Normal';
                    })
                    ->color(function ($record) {
                        $p = $record->monthlyFuelQuotaUsagePercent();
                        if ($p > 100) {
                            return 'danger';
                        }
                        if ($p >= 80) {
                            return 'warning';
                        }

                        return 'success';
                    }),
            ]);
    }
}
