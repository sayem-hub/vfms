<?php

namespace App\Filament\Resources\TripExpenseSettlements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TripExpenseSettlementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('trip_request_id')
                    ->label(fn () => __('vfms.trip_request_no'))
                    ->relationship('tripRequest', 'request_no')
                    ->required(),
                Select::make('driver_id')
                    ->relationship('driver', 'name')
                    ->required(),
                TextInput::make('advance_cash_received')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('advance_received_from_user_id')
                    ->numeric(),
                DateTimePicker::make('advance_disbursed_at'),
                TextInput::make('total_fuel_expense')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_toll_expense')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_parking_expense')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_driver_food_allowance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_emergency_repair_expense')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_other_expense')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_actual_expense')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('receipts_attachment'),
                TextInput::make('cashier_settled_by')
                    ->numeric(),
                DateTimePicker::make('settled_at'),
                TextInput::make('status')
                    ->required()
                    ->default('DRAFT_BY_DRIVER'),
            ]);
    }
}
