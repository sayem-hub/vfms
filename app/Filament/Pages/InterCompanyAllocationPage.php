<?php

namespace App\Filament\Pages;

use App\Models\CostAllocation;
use App\Services\Financial\CostAllocationService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class InterCompanyAllocationPage extends Page
{
    protected string $view = 'filament.pages.inter-company-allocation';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    public string $selectedPeriod = '';

    public function mount(): void
    {
        $this->selectedPeriod = now()->format('Y-m');
    }

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'bn' ? 'রিপোর্ট ও বিশ্লেষণ' : 'Reports & Analytics';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'bn' ? 'সিস্টার কনসার্ন খরচ বণ্টন' : 'Inter-Company Cost Allocation';
    }

    public function getTitle(): string
    {
        return app()->getLocale() === 'bn' ? 'সিস্টার কনসার্ন ভিত্তিক পরিবহন ব্যয় বণ্টন ও জার্নাল' : 'Inter-Company Fleet Cost Allocation & Journal';
    }

    public function generateJournal(int $companyId, CostAllocationService $allocationService): void
    {
        $journal = $allocationService->generateAndSaveJournal($companyId, $this->selectedPeriod);

        Notification::make()
            ->title(app()->getLocale() === 'bn' ? 'অ্যালোকেশন জার্নাল তৈরি হয়েছে' : 'Allocation Journal Created')
            ->body("Journal Reference: {$journal->journal_reference_no} (Total: ৳".number_format((float) $journal->grand_total_allocated, 2).')')
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        $allocationService = app(CostAllocationService::class);
        $matrix = $allocationService->calculateMonthlyAllocationMatrix($this->selectedPeriod);

        $finalizedJournals = CostAllocation::with(['company', 'factoryUnit', 'approvedBy'])
            ->where('allocation_period', $this->selectedPeriod)
            ->latest()
            ->get();

        return [
            'matrix' => $matrix,
            'finalizedJournals' => $finalizedJournals,
        ];
    }
}
