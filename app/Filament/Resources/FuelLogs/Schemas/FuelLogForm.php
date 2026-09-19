<?php

namespace App\Filament\Resources\FuelLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FuelLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('trip_request_id')
                    ->label(fn () => __('vfms.trip_request_no'))
                    ->relationship('tripRequest', 'request_no')
                    ->searchable()
                    ->preload(),
                Select::make('vehicle_id')
                    ->label(fn () => __('vfms.vehicle'))
                    ->relationship('vehicle', 'registration_no')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('driver_id')
                    ->label(fn () => __('vfms.driver'))
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('fuel_type')
                    ->label(fn () => __('vfms.fuel_type'))
                    ->options([
                        'DIESEL' => 'Diesel (ডিজেল)',
                        'OCTANE' => 'Octane (অকটেন)',
                        'PETROL' => 'Petrol (পেট্রোল)',
                        'CNG' => 'CNG (সিএনজি)',
                        'LPG' => 'LPG (এলপিজি)',
                    ])
                    ->default('OCTANE')
                    ->required(),
                DateTimePicker::make('refill_date')
                    ->default(now())
                    ->required(),
                TextInput::make('station_name')
                    ->label(fn () => __('vfms.filling_station'))
                    ->required(),
                TextInput::make('odometer_reading')
                    ->label(fn () => __('vfms.current_odometer'))
                    ->numeric()
                    ->required(),
                TextInput::make('fuel_quantity')
                    ->label(fn () => __('vfms.fuel_quantity'))
                    ->numeric()
                    ->required(),
                TextInput::make('unit_price')
                    ->label(fn () => __('vfms.unit_price'))
                    ->numeric()
                    ->prefix('BDT')
                    ->required(),
                TextInput::make('total_cost')
                    ->label(fn () => __('vfms.total_fuel_cost'))
                    ->numeric()
                    ->prefix('BDT')
                    ->required(),
                Select::make('payment_method')
                    ->options([
                        'PETTY_CASH_ADVANCE' => 'Petty Cash Advance (অগ্রিম ক্যাশ)',
                        'DRIVER_POCKET_REIMBURSABLE' => 'Driver Pocket Reimbursable (পরিশোধযোগ্য)',
                        'COMPANY_CREDIT_VOUCHER' => 'Company Credit Voucher (কোম্পানি ভাউচার)',
                    ])
                    ->default('PETTY_CASH_ADVANCE')
                    ->required(),

                // Mandatory 3-Point Photo Proof
                FileUpload::make('dispenser_photo')
                    ->label(fn () => __('vfms.dispenser_photo'))
                    ->disk('public')
                    ->directory('fuel/dispensers')
                    ->image(),
                FileUpload::make('odometer_photo')
                    ->label(fn () => __('vfms.odometer_photo'))
                    ->disk('public')
                    ->directory('fuel/odometers')
                    ->image(),
                FileUpload::make('receipt_memo_photo')
                    ->label(fn () => __('vfms.receipt_photo'))
                    ->disk('public')
                    ->directory('fuel/receipts')
                    ->image(),

                Toggle::make('is_efficiency_anomaly')
                    ->label('Siphon Anomaly Flagged'),
            ]);
    }
}
