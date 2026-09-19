<?php

namespace App\Filament\Resources\VehicleGateLogs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleGateLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->preload(),
                Select::make('fixed_route_id')
                    ->label(fn () => __('vfms.fixed_route'))
                    ->relationship('fixedRoute', 'route_name')
                    ->searchable()
                    ->preload(),
                Select::make('log_type')
                    ->label(fn () => __('vfms.usage_category'))
                    ->options([
                        'DEDICATED_MANAGEMENT_CAR' => 'Dedicated Management Car (ম্যানেজমেন্টের গাড়ি)',
                        'STAFF_COMMUTE_BUS' => 'Staff Commute Bus (কর্মচারী পরিবহন বাস)',
                        'MAINTENANCE_TEST_RUN' => 'Maintenance / Test Run (টেস্ট রান)',
                        'OFFICIAL_DUTY' => 'Official Duty (অফিসিয়াল ডিউটি)',
                        'OTHER' => 'Other / Emergency (অন্যান্য)',
                    ])
                    ->default('DEDICATED_MANAGEMENT_CAR')
                    ->required(),
                DatePicker::make('log_date')
                    ->label('Log Date')
                    ->default(now())
                    ->required(),
                Select::make('status')
                    ->label(fn () => __('vfms.status'))
                    ->options([
                        'OUT' => 'OUT (গেটের বাইরে চলমান)',
                        'COMPLETED' => 'COMPLETED / IN (প্রবেশ সম্পন্ন)',
                        'CANCELLED' => 'CANCELLED (বাতিল)',
                    ])
                    ->default('OUT')
                    ->required(),
                DateTimePicker::make('gate_out_time')
                    ->label(fn () => __('vfms.gate_out')),
                DateTimePicker::make('gate_in_time')
                    ->label(fn () => __('vfms.gate_in')),
                TextInput::make('out_odometer')
                    ->label(fn () => __('vfms.out_odometer'))
                    ->numeric(),
                TextInput::make('in_odometer')
                    ->label(fn () => __('vfms.in_odometer'))
                    ->numeric(),
                TextInput::make('total_km')
                    ->label(fn () => __('vfms.total_km_run'))
                    ->numeric()
                    ->suffix('KM'),
                TextInput::make('official_name')
                    ->label(fn () => __('vfms.dedicated_official'))
                    ->placeholder('e.g. Managing Director / Director SCM'),
                TextInput::make('destination')
                    ->label(fn () => __('vfms.destination'))
                    ->placeholder('e.g. Baridhara DOHS HO / Mawna Plant'),
                TextInput::make('purpose')
                    ->label(fn () => __('vfms.purpose'))
                    ->placeholder('e.g. Daily Commute / Pickup'),
                Select::make('security_guard_id')
                    ->label('Security Guard')
                    ->relationship('securityGuard', 'name')
                    ->searchable()
                    ->preload(),
                Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }
}
