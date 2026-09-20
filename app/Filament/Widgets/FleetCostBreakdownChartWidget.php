<?php

namespace App\Filament\Widgets;

use App\Services\Financial\CostPerKmService;
use Filament\Widgets\ChartWidget;

class FleetCostBreakdownChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return app()->getLocale() === 'bn' ? 'ফ্লিট ব্যয়ের আনুপাতিক চিত্র (Fleet Cost Breakdown)' : 'Fleet Cost Breakdown';
    }

    protected function getData(): array
    {
        $cpkService = app(CostPerKmService::class);
        $fleet = $cpkService->calculateFleetCpk();

        return [
            'datasets' => [
                [
                    'label' => 'Cost (BDT)',
                    'data' => [
                        $fleet['total_fuel_cost'],
                        $fleet['total_maintenance_cost'],
                        $fleet['total_operating_cost'],
                        $fleet['total_driver_cost'],
                        $fleet['total_fixed_cost'],
                    ],
                    'backgroundColor' => [
                        '#ea580c', // NZ Group Orange
                        '#dc2626', // Red
                        '#2563eb', // Blue
                        '#059669', // Emerald
                        '#7c3aed', // Purple
                    ],
                ],
            ],
            'labels' => [
                app()->getLocale() === 'bn' ? 'জ্বালানী (Fuel)' : 'Fuel',
                app()->getLocale() === 'bn' ? 'রক্ষণাবেক্ষণ ও পার্টস (Maintenance)' : 'Maintenance',
                app()->getLocale() === 'bn' ? 'টোল ও অন্যান্য (Operating)' : 'Operating Expenses',
                app()->getLocale() === 'bn' ? 'চালকের বেতন (Driver Salary)' : 'Driver Salary',
                app()->getLocale() === 'bn' ? 'ফিক্সড / রেন্টাল (Fixed Cost)' : 'Fixed / Rental',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
