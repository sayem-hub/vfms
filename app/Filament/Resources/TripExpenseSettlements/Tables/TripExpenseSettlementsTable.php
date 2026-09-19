<?php

namespace App\Filament\Resources\TripExpenseSettlements\Tables;

use App\Services\Settlement\TripExpenseSettlementService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TripExpenseSettlementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tripRequest.request_no')
                    ->label(fn () => __('vfms.trip_request_no'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('driver.name')
                    ->label(fn () => __('vfms.driver_name'))
                    ->searchable(),
                TextColumn::make('advance_cash_received')
                    ->label(fn () => __('vfms.advance_cash_received'))
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('total_fuel_expense')
                    ->label(fn () => __('vfms.total_fuel_cost'))
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('total_toll_expense')
                    ->label(fn () => __('vfms.total_toll_expense'))
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('total_actual_expense')
                    ->label(fn () => __('vfms.total_actual_expense'))
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('balance_amount')
                    ->label(fn () => __('vfms.settlement_balance'))
                    ->state(function ($record) {
                        $bal = (float) $record->balance_amount;
                        if ($bal > 0) {
                            return '+BDT '.number_format($bal, 2).' ('.__('vfms.balance_refundable_to_company').')';
                        } elseif ($bal < 0) {
                            return '-BDT '.number_format(abs($bal), 2).' ('.__('vfms.balance_payable_to_driver').')';
                        }

                        return 'BDT 0.00 (Balanced)';
                    })
                    ->badge()
                    ->color(fn ($record) => (float) $record->balance_amount >= 0 ? 'success' : 'danger'),
                TextColumn::make('status')
                    ->label(fn () => __('vfms.status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'SETTLED_BY_CASHIER' => 'success',
                        'AUDITED_BY_TRANSPORT' => 'info',
                        'DRAFT_BY_DRIVER' => 'warning',
                        'DISPUTED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('settled_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'DRAFT_BY_DRIVER' => 'Draft by Driver',
                        'AUDITED_BY_TRANSPORT' => 'Audited by Transport',
                        'SETTLED_BY_CASHIER' => 'Settled by Cashier',
                        'DISPUTED' => 'Disputed',
                    ]),
            ])
            ->recordActions([
                Action::make('recalculate')
                    ->label('Recalculate (পুনরায় হিসাব)')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('info')
                    ->action(function ($record, TripExpenseSettlementService $service) {
                        $service->syncAndCalculate($record);
                        Notification::make()
                            ->title('Expenses Recalculated')
                            ->body("Actual: BDT {$record->total_actual_expense}, Balance: BDT {$record->balance_amount}")
                            ->send();
                    }),
                Action::make('settle_cashier')
                    ->label('Settle Cash (ক্যাশ সমন্বয় সম্পন্ন)')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'SETTLED_BY_CASHIER')
                    ->requiresConfirmation()
                    ->action(function ($record, TripExpenseSettlementService $service) {
                        $service->settleByCashier($record, auth()->id() ?? 1);
                        Notification::make()
                            ->title('Trip Settlement Finalized')
                            ->body('Status updated to Settled by Cashier.')
                            ->success()
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
