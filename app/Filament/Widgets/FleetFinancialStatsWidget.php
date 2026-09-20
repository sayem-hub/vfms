<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Services\Financial\CostPerKmService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FleetFinancialStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $cpkService = app(CostPerKmService::class);
        $fleetCpk = $cpkService->calculateFleetCpk();

        // Calculate quota rate
        $quotaVehicles = Vehicle::where('is_active', true)
            ->whereNotNull('monthly_fuel_quota_liters')
            ->where('monthly_fuel_quota_liters', '>', 0)
            ->get();

        $totalQuota = (float) $quotaVehicles->sum('monthly_fuel_quota_liters');
        $totalConsumed = 0.0;
        foreach ($quotaVehicles as $v) {
            $totalConsumed += $v->monthlyFuelConsumedLiters();
        }

        $quotaUsagePercent = $totalQuota > 0 ? round(($totalConsumed / $totalQuota) * 100, 1) : 0;

        return [
            Stat::make(
                label: app()->getLocale() === 'bn' ? 'চলতি মাসের ফ্লিট পরিচালন ব্যয়' : 'Monthly Fleet Operating Spend',
                value: '৳ '.number_format($fleetCpk['grand_total_fleet_spend'], 0)
            )
                ->description(app()->getLocale() === 'bn' ? 'জ্বালানী, মেরামত ও রানিং ব্যয়' : 'Fuel, servicing & operating')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make(
                label: app()->getLocale() === 'bn' ? 'গড় কস্ট পার কিমি (CPK)' : 'Average Cost Per KM (CPK)',
                value: '৳ '.number_format($fleetCpk['fleet_avg_cpk'], 2).' /KM'
            )
                ->description(app()->getLocale() === 'bn' ? 'প্রতি কিমি মোট ব্যয়' : 'Total cost per kilometer')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($fleetCpk['fleet_avg_cpk'] > 35 ? 'warning' : 'success'),

            Stat::make(
                label: app()->getLocale() === 'bn' ? 'চলতি মাসের মোট দূরত্ব' : 'Monthly Fleet Distance Run',
                value: number_format($fleetCpk['total_fleet_km'], 0).' KM'
            )
                ->description(app()->getLocale() === 'bn' ? 'ট্রিপ ও গেট লগ অনুযায়ী' : 'From trips and gate logs')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),

            Stat::make(
                label: app()->getLocale() === 'bn' ? 'ম্যানেজমেন্ট ফুয়েল কোটা ব্যবহার' : 'Executive Fuel Quota Used',
                value: "{$quotaUsagePercent}%"
            )
                ->description(
                    app()->getLocale() === 'bn'
                        ? "ব্যবহৃত: {$totalConsumed}L / কোটা: {$totalQuota}L"
                        : "Used: {$totalConsumed}L / Quota: {$totalQuota}L"
                )
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($quotaUsagePercent > 90 ? 'danger' : ($quotaUsagePercent > 75 ? 'warning' : 'success')),
        ];
    }
}
