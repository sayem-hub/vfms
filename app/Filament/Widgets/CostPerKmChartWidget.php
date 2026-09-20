<?php

namespace App\Filament\Widgets;

use App\Services\Financial\CostPerKmService;
use Filament\Widgets\ChartWidget;

class CostPerKmChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return app()->getLocale() === 'bn' ? 'ক্যাটাগরি ভিত্তিক প্রতি কিমি খরচ (CPK Benchmark)' : 'Cost Per KM (CPK) Benchmark';
    }

    protected function getData(): array
    {
        $cpkService = app(CostPerKmService::class);
        $benchmark = $cpkService->calculateCategoryBenchmark();

        $labels = [
            'SEDAN_CAR' => app()->getLocale() === 'bn' ? 'সেডান কার' : 'Sedan Car',
            'MICROBUS' => app()->getLocale() === 'bn' ? 'মাইক্রোবাস' : 'Microbus',
            'STAFF_BUS' => app()->getLocale() === 'bn' ? 'স্টাফ বাস' : 'Staff Bus',
            'COVERED_VAN_5T' => app()->getLocale() === 'bn' ? 'কাভার্ড ভ্যান' : 'Covered Van',
            'AMBULANCE' => app()->getLocale() === 'bn' ? 'এ্যাম্বুলেন্স' : 'Ambulance',
        ];

        $data = [];
        $chartLabels = [];

        foreach ($labels as $key => $label) {
            $chartLabels[] = $label;
            $data[] = $benchmark[$key]['cpk'] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => app()->getLocale() === 'bn' ? 'কস্ট পার কিমি (৳/KM)' : 'Cost Per KM (BDT/KM)',
                    'data' => $data,
                    'backgroundColor' => [
                        '#3b82f6',
                        '#6366f1',
                        '#ea580c',
                        '#10b981',
                        '#f43f5e',
                    ],
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $chartLabels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
