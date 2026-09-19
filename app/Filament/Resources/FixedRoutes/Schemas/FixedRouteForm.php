<?php

namespace App\Filament\Resources\FixedRoutes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FixedRouteForm
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
                TextInput::make('route_name')
                    ->label(fn () => __('vfms.route_name'))
                    ->placeholder('e.g. Joydebpur ➔ BK Bari Plant Staff Commute')
                    ->required(),
                TextInput::make('route_code')
                    ->label(fn () => __('vfms.route_code'))
                    ->placeholder('e.g. R-01'),
                TextInput::make('origin_name')
                    ->label(fn () => __('vfms.origin'))
                    ->placeholder('e.g. Joydebpur Chowrasta')
                    ->required(),
                TextInput::make('destination_name')
                    ->label(fn () => __('vfms.destination'))
                    ->placeholder('e.g. NZ Group BK Bari Industrial Park')
                    ->required(),
                TextInput::make('standard_distance_km')
                    ->label(fn () => __('vfms.expected_distance'))
                    ->numeric()
                    ->suffix('KM'),
                TextInput::make('shift_name')
                    ->label(fn () => __('vfms.shift_name'))
                    ->default('General Shift'),
                TextInput::make('scheduled_departure_time')
                    ->label(fn () => __('vfms.scheduled_departure'))
                    ->placeholder('e.g. 06:30 AM'),
                TextInput::make('scheduled_return_time')
                    ->label(fn () => __('vfms.scheduled_return'))
                    ->placeholder('e.g. 06:30 PM'),
                Select::make('assigned_vehicle_id')
                    ->label(fn () => __('vfms.assigned_vehicle'))
                    ->relationship('assignedVehicle', 'registration_no')
                    ->searchable()
                    ->preload(),
                Select::make('assigned_driver_id')
                    ->label(fn () => __('vfms.assigned_driver'))
                    ->relationship('assignedDriver', 'name')
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->label(fn () => __('vfms.status'))
                    ->default(true),
                Textarea::make('stoppages')
                    ->label(fn () => __('vfms.stoppages'))
                    ->placeholder('e.g. Chowrasta -> Shibbari -> Rajendrapur -> BK Bari Plant')
                    ->columnSpanFull(),
                Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }
}
