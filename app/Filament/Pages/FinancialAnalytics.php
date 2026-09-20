<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use App\Services\Financial\CostPerKmService;
use App\Services\Financial\TotalCostOfOwnershipService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class FinancialAnalytics extends Page
{
    protected string $view = 'filament.pages.financial-analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'রিপোর্ট ও বিশ্লেষণ' : 'Reports & Analytics';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'আর্থিক বিশ্লেষণ ও টিসিও (TCO)' : 'Financial Analytics & TCO';
    }

    public function getTitle(): string
    {
        return app()->getLocale() === 'bn' ? 'ফ্লিট আর্থিক বিশ্লেষণ, CPK ও টিসিও (TCO)' : 'Fleet Financial Analytics, CPK & TCO';
    }

    public function getViewData(): array
    {
        $cpkService = app(CostPerKmService::class);
        $tcoService = app(TotalCostOfOwnershipService::class);

        $fleetCpk = $cpkService->calculateFleetCpk();
        $fleetTco = $tcoService->getFleetTcoSummary();
        $recommendations = $tcoService->getRepairVsReplaceRecommendations();
        $categoryBenchmarks = $cpkService->calculateCategoryBenchmark();

        $vehicles = Vehicle::where('is_active', true)->get();
        $vehicleTcoList = [];
        foreach ($vehicles as $v) {
            $vehicleTcoList[] = $tcoService->getVehicleTco($v);
        }

        return [
            'fleetCpk' => $fleetCpk,
            'fleetTco' => $fleetTco,
            'recommendations' => $recommendations,
            'categoryBenchmarks' => $categoryBenchmarks,
            'vehicleTcoList' => $vehicleTcoList,
        ];
    }
}
