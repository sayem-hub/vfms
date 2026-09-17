<?php

namespace App\Filament\Resources\TripRequisitions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TripRequisitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('requisition_no')
                    ->label(fn () => __('vfms.requisition_no'))
                    ->default(fn () => 'REQ-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)))
                    ->required()
                    ->unique(ignoreRecord: true),
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
                Select::make('requester_id')
                    ->label('Requester')
                    ->relationship('requester', 'name')
                    ->searchable()
                    ->default(fn () => auth()->id())
                    ->required(),
                Select::make('vehicle_id')
                    ->label(fn () => __('vfms.vehicle'))
                    ->relationship('vehicle', 'registration_no')
                    ->searchable()
                    ->preload(),
                Select::make('driver_id')
                    ->label(fn () => __('vfms.driver'))
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('trip_type')
                    ->label(fn () => __('vfms.trip_type'))
                    ->options([
                        'OFFICIAL_DUTY' => 'Official Duty (অফিসিয়াল দায়িত্ব)',
                        'EMPLOYEE_COMMUTE' => 'Employee Commute (কর্মচারী যাতায়াত)',
                        'EXPORT_SHIPMENT' => 'Export Shipment Covered Van (রপ্তানি চালান)',
                        'EMERGENCY_AMBULANCE' => 'Emergency Ambulance (জরুরি এ্যাম্বুলেন্স)',
                        'GUEST_QC_PICKUP' => 'Buyer / QC / Auditor Pickup (অডিটর / বায়ার ভিজিট)',
                        'PERSONAL_USE' => 'Personal Use (ব্যক্তিগত ব্যবহার)',
                    ])
                    ->default('OFFICIAL_DUTY')
                    ->required(),
                Textarea::make('purpose')
                    ->label(fn () => __('vfms.purpose'))
                    ->columnSpanFull(),
                TextInput::make('origin_name')
                    ->label(fn () => __('vfms.origin'))
                    ->required(),
                TextInput::make('destination_name')
                    ->label(fn () => __('vfms.destination'))
                    ->required(),
                TextInput::make('origin_latitude')
                    ->numeric(),
                TextInput::make('origin_longitude')
                    ->numeric(),
                TextInput::make('destination_latitude')
                    ->numeric(),
                TextInput::make('destination_longitude')
                    ->numeric(),
                DateTimePicker::make('scheduled_start_time')
                    ->required(),
                DateTimePicker::make('scheduled_end_time'),
                DateTimePicker::make('actual_start_time'),
                DateTimePicker::make('actual_end_time'),
                TextInput::make('start_odometer')
                    ->label(fn () => __('vfms.start_odometer'))
                    ->numeric(),
                TextInput::make('end_odometer')
                    ->label(fn () => __('vfms.end_odometer'))
                    ->numeric(),
                TextInput::make('claimed_distance_km')
                    ->label(fn () => __('vfms.claimed_distance'))
                    ->numeric(),
                TextInput::make('expected_distance_km')
                    ->label(fn () => __('vfms.expected_distance'))
                    ->numeric(),
                Toggle::make('is_distance_anomaly')
                    ->label(fn () => __('vfms.distance_anomaly')),
                Textarea::make('anomaly_justification')
                    ->label(fn () => __('vfms.anomaly_justification'))
                    ->columnSpanFull(),

                // Custom ERP Verification Fields
                TextInput::make('erp_requisition_no')
                    ->label(fn () => __('vfms.erp_requisition_no')),
                FileUpload::make('erp_requisition_copy')
                    ->label(fn () => __('vfms.erp_requisition_copy'))
                    ->disk('public')
                    ->directory('erp/requisitions')
                    ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword']),
                TextInput::make('erp_gatepass_no')
                    ->label(fn () => __('vfms.erp_gatepass_no')),
                FileUpload::make('erp_gatepass_copy')
                    ->label(fn () => __('vfms.erp_gatepass_copy'))
                    ->disk('public')
                    ->directory('erp/gatepasses')
                    ->acceptedFileTypes(['image/*', 'application/pdf']),

                Select::make('status')
                    ->label(fn () => __('vfms.status'))
                    ->options([
                        'SUBMITTED' => 'Submitted (দাখিলকৃত)',
                        'HOD_APPROVED' => 'HOD Approved (অনুমোদিত)',
                        'DISPATCHED' => 'Dispatched (রওনা হয়েছে)',
                        'GATE_OUT' => 'Gate Out (ফ্যাক্টরি প্রস্থান)',
                        'IN_TRIP' => 'In Trip (চলমান)',
                        'GATE_IN' => 'Gate In (ফ্যাক্টরি প্রত্যাবর্তন)',
                        'COMPLETED' => 'Completed (সম্পন্ন)',
                        'CANCELLED' => 'Cancelled (বাতিল)',
                        'REJECTED' => 'Rejected (প্রত্যাখ্যাত)',
                    ])
                    ->default('SUBMITTED')
                    ->required(),
            ]);
    }
}
