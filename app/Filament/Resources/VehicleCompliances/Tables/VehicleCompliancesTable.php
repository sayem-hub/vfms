<?php

namespace App\Filament\Resources\VehicleCompliances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleCompliancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.registration_no')
                    ->label(fn () => __('vfms.registration_no'))
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_type')
                    ->label('Document Type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('certificate_number')
                    ->label('Certificate No')
                    ->searchable(),
                TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('days_remaining')
                    ->label('Days Remaining / মেয়াদ বাকি')
                    ->state(fn ($record) => $record->isExpired() ? 'মেয়াদোত্তীর্ণ (Expired)' : $record->daysRemaining().' দিন ('.$record->daysRemaining().' days)')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->isExpired() => 'danger',
                        $record->daysRemaining() <= 15 => 'danger',
                        $record->daysRemaining() <= 30 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('renewal_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('alert_60d_sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('alert_30d_sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('alert_15d_sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('alert_7d_sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
