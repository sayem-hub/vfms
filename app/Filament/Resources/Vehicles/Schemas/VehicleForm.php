<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Select::make('factory_unit_id')
                    ->relationship('factoryUnit', 'name'),
                TextInput::make('registration_no')
                    ->required(),
                TextInput::make('vehicle_type')
                    ->required()
                    ->default('SEDAN_CAR'),
                Select::make('usage_category')
                    ->label(fn () => __('vfms.usage_category'))
                    ->options([
                        'GENERAL_POOL' => 'General Pool (সাধারণ পুল)',
                        'DEDICATED_MANAGEMENT' => 'Dedicated Management (ম্যানেজমেন্টের নির্ধারিত)',
                        'STAFF_COMMUTE_BUS' => 'Staff Commute Bus (স্টাফ বাস)',
                        'FACTORY_LOGISTICS' => 'Factory Logistics (কারখানা পণ্য পরিবহন)',
                        'EMERGENCY_AMBULANCE' => 'Emergency Ambulance (এ্যাম্বুলেন্স)',
                    ])
                    ->default('GENERAL_POOL')
                    ->required(),
                TextInput::make('dedicated_to_official')
                    ->label(fn () => __('vfms.dedicated_official'))
                    ->placeholder('e.g. Managing Director / Director SCM'),

                TextInput::make('ownership_type')
                    ->required()
                    ->default('COMPANY_OWNED'),
                TextInput::make('fuel_type')
                    ->required()
                    ->default('OCTANE'),
                TextInput::make('fuel_payer')
                    ->required()
                    ->default('COMPANY'),
                TextInput::make('maintenance_payer')
                    ->required()
                    ->default('COMPANY'),
                TextInput::make('driver_payer')
                    ->required()
                    ->default('COMPANY'),
                TextInput::make('monthly_fuel_quota_liters')
                    ->numeric(),
                TextInput::make('monthly_fixed_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('rate_per_km')
                    ->numeric(),
                TextInput::make('current_odometer')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('fuel_capacity_liters')
                    ->required()
                    ->numeric()
                    ->default(50),
                TextInput::make('expected_km_per_liter')
                    ->required()
                    ->numeric()
                    ->default(8),
                TextInput::make('brand'),
                TextInput::make('model_name'),
                TextInput::make('model_year'),
                TextInput::make('chassis_number'),
                TextInput::make('engine_number'),
                TextInput::make('status')
                    ->required()
                    ->default('AVAILABLE'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
