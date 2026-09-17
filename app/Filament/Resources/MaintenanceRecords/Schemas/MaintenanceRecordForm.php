<?php

namespace App\Filament\Resources\MaintenanceRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MaintenanceRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('work_order_no')
                    ->required(),
                Select::make('vehicle_id')
                    ->relationship('vehicle', 'id')
                    ->required(),
                TextInput::make('maintenance_type')
                    ->required()
                    ->default('SCHEDULED_PREVENTIVE'),
                TextInput::make('workshop_type')
                    ->required()
                    ->default('FACTORY_IN_HOUSE_WORKSHOP'),
                TextInput::make('vendor_name'),
                TextInput::make('vendor_phone')
                    ->tel(),
                Textarea::make('vendor_address')
                    ->columnSpanFull(),
                TextInput::make('odometer_at_service')
                    ->required()
                    ->numeric(),
                DatePicker::make('service_date')
                    ->required(),
                Textarea::make('service_description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('parts_total_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('labor_total_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('grand_total_cost')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Toggle::make('requires_old_parts_surrender')
                    ->required(),
                Toggle::make('is_old_parts_surrendered')
                    ->required(),
                TextInput::make('store_acknowledged_by')
                    ->numeric(),
                DateTimePicker::make('store_acknowledged_at'),
                TextInput::make('erp_pr_po_no'),
                TextInput::make('erp_document_copy'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('PENDING_PARTS_SURRENDER'),
            ]);
    }
}
