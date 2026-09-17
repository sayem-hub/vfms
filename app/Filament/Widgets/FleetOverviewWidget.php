<?php

namespace App\Filament\Widgets;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\TripRequisition;
use App\Models\Vehicle;
use App\Models\VehicleCompliance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FleetOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeVehiclesCount = Vehicle::where('is_active', true)->count();
        $activeDriversCount = Driver::where('is_active', true)->count();

        $distanceAnomaliesCount = TripRequisition::where('is_distance_anomaly', true)->count();
        $fuelAnomaliesCount = FuelLog::where('is_efficiency_anomaly', true)->count();
        $totalAnomalies = $distanceAnomaliesCount + $fuelAnomaliesCount;

        $expiredOrExpiringSoonCount = VehicleCompliance::where('expiry_date', '<=', now()->addDays(30))->count();

        return [
            Stat::make(
                label: app()->getLocale() === 'bn' ? 'সক্রিয় যানবাহন' : 'Active Fleet Vehicles',
                value: (string) $activeVehiclesCount
            )
                ->description(app()->getLocale() === 'bn' ? 'মোট নিবন্ধিত গাড়ি' : 'Total fleet registered')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),

            Stat::make(
                label: app()->getLocale() === 'bn' ? 'নিয়োজিত ড্রাইভার' : 'Active Drivers',
                value: (string) $activeDriversCount
            )
                ->description(app()->getLocale() === 'bn' ? 'কোম্পানি ও ভেন্ডর চালক' : 'Payroll & vendor drivers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make(
                label: app()->getLocale() === 'bn' ? 'সন্দেহজনক অডিট সতর্কতা' : 'Flagged Cost Anomalies',
                value: (string) $totalAnomalies
            )
                ->description(
                    app()->getLocale() === 'bn'
                        ? "দূরত্ব: {$distanceAnomaliesCount}, জ্বালানী: {$fuelAnomaliesCount}"
                        : "Distance: {$distanceAnomaliesCount}, Fuel: {$fuelAnomaliesCount}"
                )
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalAnomalies > 0 ? 'danger' : 'success'),

            Stat::make(
                label: app()->getLocale() === 'bn' ? 'বিআরটিএ মেয়াদ সতর্কতা' : 'BRTA Expiry Radar (≤30d)',
                value: (string) $expiredOrExpiringSoonCount
            )
                ->description(app()->getLocale() === 'bn' ? 'জরুরি নবায়ন প্রয়োজন' : 'Fitness, tax token, test')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($expiredOrExpiringSoonCount > 0 ? 'warning' : 'success'),
        ];
    }
}
