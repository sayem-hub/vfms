<?php

namespace App\Filament\Resources\TripRequisitions\Tables;

use App\Services\Audit\DistanceAuditService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TripRequisitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requisition_no')
                    ->label(fn () => __('vfms.requisition_no'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle.registration_no')
                    ->label(fn () => __('vfms.registration_no'))
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('driver.name')
                    ->label(fn () => __('vfms.driver_name'))
                    ->searchable(),
                TextColumn::make('origin_name')
                    ->label(fn () => __('vfms.origin'))
                    ->searchable(),
                TextColumn::make('destination_name')
                    ->label(fn () => __('vfms.destination'))
                    ->searchable(),
                TextColumn::make('claimed_distance_km')
                    ->label(fn () => __('vfms.claimed_distance'))
                    ->suffix(' KM')
                    ->sortable(),
                TextColumn::make('expected_distance_km')
                    ->label(fn () => __('vfms.expected_distance'))
                    ->suffix(' KM')
                    ->sortable(),
                TextColumn::make('distance_variance_percentage')
                    ->label(fn () => __('vfms.variance'))
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => (float) $state > 15 ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('is_distance_anomaly')
                    ->label(fn () => __('vfms.distance_anomaly'))
                    ->badge()
                    ->state(fn ($record) => $record->is_distance_anomaly ? '⚠️ সতর্কতা (Anomaly >15%)' : 'স্বাভাবিক (Normal)')
                    ->color(fn ($record) => $record->is_distance_anomaly ? 'danger' : 'success'),
                TextColumn::make('erp_gatepass_no')
                    ->label(fn () => __('vfms.erp_gatepass_no'))
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('status')
                    ->label(fn () => __('vfms.status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'COMPLETED' => 'success',
                        'IN_TRIP', 'DISPATCHED' => 'info',
                        'HOD_APPROVED' => 'primary',
                        'SUBMITTED' => 'warning',
                        'CANCELLED', 'REJECTED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('scheduled_start_time')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'SUBMITTED' => 'Submitted',
                        'HOD_APPROVED' => 'HOD Approved',
                        'DISPATCHED' => 'Dispatched',
                        'IN_TRIP' => 'In Trip',
                        'COMPLETED' => 'Completed',
                        'CANCELLED' => 'Cancelled',
                    ]),
                SelectFilter::make('is_distance_anomaly')
                    ->label('Distance Anomaly')
                    ->options([
                        '1' => 'Flagged Anomaly (>15%)',
                        '0' => 'Normal Distance',
                    ]),
            ])
            ->recordActions([
                Action::make('audit_distance')
                    ->label('Audit Distance (দূরত্ব অডিট)')
                    ->icon(Heroicon::OutlinedCalculator)
                    ->color('warning')
                    ->action(function ($record, DistanceAuditService $auditService) {
                        $auditService->auditTrip($record);
                        Notification::make()
                            ->title('Distance Audited Successfully')
                            ->body($record->is_distance_anomaly
                                ? "⚠️ Flagged Anomaly! Variance: {$record->distance_variance_percentage}%"
                                : "✅ Normal trip distance verified. Variance: {$record->distance_variance_percentage}%")
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
