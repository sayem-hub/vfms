<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label(fn () => __('vfms.app_name'))
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('factory_unit_id')
                    ->label(fn () => __('vfms.factory_unit'))
                    ->relationship('factoryUnit', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label(fn () => __('vfms.driver_name'))
                    ->required(),
                TextInput::make('office_id_card')
                    ->label(fn () => __('vfms.office_id_card'))
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(fn () => __('vfms.phone'))
                    ->tel()
                    ->required(),
                TextInput::make('nid_number')
                    ->label(fn () => __('vfms.nid_number')),
                FileUpload::make('photo')
                    ->label(fn () => __('vfms.driver_photo'))
                    ->image()
                    ->disk('public')
                    ->directory('drivers/photos')
                    ->avatar(),
                TextInput::make('license_number')
                    ->label(fn () => __('vfms.license_number'))
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('license_type')
                    ->label(fn () => __('vfms.license_type'))
                    ->options([
                        'LIGHT' => 'Light (হালকা)',
                        'MEDIUM' => 'Medium (মাঝারি)',
                        'HEAVY' => 'Heavy (ভারী)',
                        'MOTORCYCLE' => 'Motorcycle (মোটরসাইকেল)',
                    ])
                    ->default('LIGHT')
                    ->required(),
                DatePicker::make('license_expiry_date')
                    ->label(fn () => __('vfms.license_expiry'))
                    ->required(),
                FileUpload::make('license_scanned_copy')
                    ->label('License Document Copy')
                    ->disk('public')
                    ->directory('drivers/licenses')
                    ->acceptedFileTypes(['image/*', 'application/pdf']),
                Select::make('employment_type')
                    ->label(fn () => __('vfms.employment_type'))
                    ->options([
                        'COMPANY_PAYROLL' => 'Company Payroll (কোম্পানি নিয়মিত বেতন)',
                        'VENDOR_DRIVER' => 'Vendor Driver (ভেন্ডর চালক)',
                        'PERSONAL_DRIVER' => 'Personal Driver (ব্যক্তিগত চালক)',
                        'DAILY_WAGE' => 'Daily Wage (দৈনিক হাজিরা)',
                    ])
                    ->default('COMPANY_PAYROLL')
                    ->required(),
                TextInput::make('salary')
                    ->numeric()
                    ->prefix('BDT'),
                Select::make('current_vehicle_id')
                    ->label(fn () => __('vfms.current_vehicle'))
                    ->relationship('currentVehicle', 'registration_no')
                    ->searchable()
                    ->preload(),
                Select::make('user_id')
                    ->label('Linked System User')
                    ->relationship('user', 'name')
                    ->searchable(),
                Select::make('preferred_locale')
                    ->label(fn () => __('vfms.language'))
                    ->options([
                        'bn' => 'বাংলা (Bengali)',
                        'en' => 'English',
                    ])
                    ->default('bn')
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
