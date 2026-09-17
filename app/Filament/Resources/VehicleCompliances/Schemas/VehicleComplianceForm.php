<?php

namespace App\Filament\Resources\VehicleCompliances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleComplianceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vehicle_id')
                    ->relationship('vehicle', 'id')
                    ->required(),
                TextInput::make('document_type')
                    ->required(),
                TextInput::make('certificate_number')
                    ->required(),
                DatePicker::make('issue_date')
                    ->required(),
                DatePicker::make('expiry_date')
                    ->required(),
                TextInput::make('document_attachment'),
                TextInput::make('renewal_cost')
                    ->numeric()
                    ->prefix('$'),
                DateTimePicker::make('alert_60d_sent_at'),
                DateTimePicker::make('alert_30d_sent_at'),
                DateTimePicker::make('alert_15d_sent_at'),
                DateTimePicker::make('alert_7d_sent_at'),
            ]);
    }
}
