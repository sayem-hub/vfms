<?php

namespace App\Filament\Resources\MaintenanceRecords\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_order_no')
                    ->label(fn () => __('vfms.work_order_no'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle.registration_no')
                    ->label(fn () => __('vfms.registration_no'))
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('workshop_type')
                    ->label(fn () => __('vfms.workshop'))
                    ->badge(),
                TextColumn::make('vendor_name')
                    ->searchable(),
                TextColumn::make('service_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('grand_total_cost')
                    ->label('Total Cost')
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('is_old_parts_surrendered')
                    ->label(fn () => __('vfms.scrap_parts_surrender'))
                    ->badge()
                    ->state(fn ($record) => $record->requires_old_parts_surrender
                        ? ($record->is_old_parts_surrendered ? '✅ স্টোরে জমা সম্পন্ন (Surrendered)' : '⚠️ স্টোরে জমা বাকি (Pending Scrap)')
                        : 'প্রযোজ্য নয় (N/A)')
                    ->color(fn ($record) => $record->requires_old_parts_surrender
                        ? ($record->is_old_parts_surrendered ? 'success' : 'danger')
                        : 'gray'),
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'PAID' => 'success',
                        'READY_FOR_PAYMENT' => 'info',
                        'PENDING_PARTS_SURRENDER' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('erp_pr_po_no')
                    ->label('ERP PR/PO No')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->options([
                        'PENDING_PARTS_SURRENDER' => 'Pending Parts Surrender',
                        'READY_FOR_PAYMENT' => 'Ready for Payment',
                        'PAID' => 'Paid',
                    ]),
            ])
            ->recordActions([
                Action::make('confirm_scrap')
                    ->label('Store Acknowledge (স্টোরে পার্টস গ্রহণ)')
                    ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
                    ->color('warning')
                    ->visible(fn ($record) => $record->requires_old_parts_surrender && ! $record->is_old_parts_surrendered)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_old_parts_surrendered' => true,
                            'store_acknowledged_by' => auth()->id() ?? 1,
                            'store_acknowledged_at' => now(),
                            'payment_status' => 'READY_FOR_PAYMENT',
                        ]);
                        Notification::make()
                            ->title('Old Parts Surrender Confirmed')
                            ->body('Store receipt recorded. Payment status unlocked to Ready For Payment.')
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
