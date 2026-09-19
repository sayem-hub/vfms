<?php

namespace App\Filament\Resources\MaintenanceRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. ডিজিটাল প্রি-রিকুইজিশন ও এডমিন অনুমোদন (Pre-Requisition & Admin Approval)')
                    ->description('হাত দিয়ে লেখা ম্যানুয়াল কাগজের পরিবর্তে VFMS-এ ডিজিটাল প্রি-রিকুইজিশন ও এডমিন হেড অনুমোদন')
                    ->schema([
                        TextInput::make('pre_requisition_no')
                            ->label(fn () => __('vfms.pre_requisition_no'))
                            ->default(fn () => 'MPR-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('transport_requester_id')
                            ->label('ট্রান্সপোর্ট রিকুয়েস্টার (Transport Requester)')
                            ->relationship('transportRequester', 'name')
                            ->searchable()
                            ->default(fn () => auth()->id())
                            ->required(),
                        TextInput::make('estimated_cost')
                            ->label(fn () => __('vfms.estimated_cost'))
                            ->numeric()
                            ->prefix('৳')
                            ->default(0),
                        Select::make('admin_head_id')
                            ->label('এডমিন হেড (Admin Head)')
                            ->relationship('adminHead', 'name')
                            ->searchable(),
                        Select::make('admin_approval_status')
                            ->label(fn () => __('vfms.admin_approval_status'))
                            ->options([
                                'PENDING_ADMIN_APPROVAL' => 'Pending Admin Review (বিচারাধীন)',
                                'APPROVED_BY_ADMIN' => 'Approved by Admin (অনুমোদিত)',
                                'REJECTED_BY_ADMIN' => 'Rejected by Admin (প্রত্যাখ্যাত)',
                                'DRAFT' => 'Draft (খসড়া)',
                            ])
                            ->default('PENDING_ADMIN_APPROVAL')
                            ->required(),
                        DateTimePicker::make('admin_approved_at')
                            ->label('অনুমোদনের সময় (Approved At)'),
                        Textarea::make('admin_remarks')
                            ->label('এডমিন হেডের মন্তব্য (Admin Remarks)')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('2. সেন্ট্রাল স্টোর ও ইআরপি রিকুইজিশন ট্যাগিং (Store ERP Requisition)')
                    ->description('অনুমোদনের পর সেন্ট্রাল স্টোর থেকে ERP-তে এন্ট্রি করে SRQ বা RQSN নম্বর ট্যাগ করা')
                    ->schema([
                        Select::make('erp_requisition_type')
                            ->label(fn () => __('vfms.erp_requisition_type'))
                            ->options([
                                'SERVICE_REQUISITION' => 'Service Requisition (SRQ - যেমন: ডেন্ট, পেইন্ট, ইএফআই সার্ভিস)',
                                'PARTS_REQUISITION' => 'Parts / Purchase Requisition (RQSN - যেমন: ফিল্টার, ব্যাটারি, টায়ার)',
                            ]),
                        TextInput::make('erp_requisition_no')
                            ->label(fn () => __('vfms.erp_requisition_no'))
                            ->placeholder('e.g. NAZBL-SRQ-26-00389 or NAZBL-RQSN-26-02116'),
                        DatePicker::make('erp_requisition_date')
                            ->label(fn () => __('vfms.erp_requisition_date')),
                        FileUpload::make('erp_requisition_copy')
                            ->label(fn () => __('vfms.erp_requisition_copy'))
                            ->disk('public')
                            ->directory('erp/maintenance_requisitions')
                            ->acceptedFileTypes(['image/*', 'application/pdf']),
                        Select::make('erp_requisition_tagged_by')
                            ->label('ট্যাগ করেছেন (Tagged By)')
                            ->relationship('erpTaggedBy', 'name')
                            ->searchable(),
                        DateTimePicker::make('erp_requisition_tagged_at')
                            ->label('ট্যাগ করার তারিখ/সময়'),
                    ])->columns(2),

                Section::make('3. মেরামত কাজ ও ওয়ার্কশপ বিবরণ (Service Details & Workshop)')
                    ->schema([
                        TextInput::make('work_order_no')
                            ->label(fn () => __('vfms.work_order_no'))
                            ->default(fn () => 'WO-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)))
                            ->required(),
                        Select::make('vehicle_id')
                            ->label(fn () => __('vfms.vehicle'))
                            ->relationship('vehicle', 'registration_no')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('maintenance_type')
                            ->label(fn () => __('vfms.service_type'))
                            ->options([
                                'SCHEDULED_PREVENTIVE' => 'Scheduled Preventive Maintenance (নিয়মিত রক্ষণাবেক্ষণ)',
                                'EMERGENCY_BREAKDOWN' => 'Emergency Breakdown (ব্রেকডাউন মেরামত)',
                                'ACCIDENT_BODYWORK' => 'Accident & Body Dent/Paint (দুর্ঘটনা ও ডেন্ট/পেইন্ট)',
                                'TYRE_BATTERY_REPLACEMENT' => 'Tyre / Battery Replacement (টায়ার বা ব্যাটারি)',
                                'AC_SERVICING' => 'AC Servicing & Gas (এসি সার্ভিসিং)',
                                'ELECTRICAL_EFI' => 'Electrical & EFI Tuning (ইলেকট্রিক্যাল ও ইএফআই)',
                            ])
                            ->default('SCHEDULED_PREVENTIVE')
                            ->required(),
                        Select::make('workshop_type')
                            ->label(fn () => __('vfms.workshop'))
                            ->options([
                                'FACTORY_IN_HOUSE_WORKSHOP' => 'Factory In-House Workshop (কারখানা নিজস্ব ওয়ার্কশপ)',
                                'EXTERNAL_VENDOR_GARAGE' => 'External Vendor Garage (বহিরাগত ভেন্ডর গ্যারেজ)',
                            ])
                            ->default('FACTORY_IN_HOUSE_WORKSHOP')
                            ->required(),
                        TextInput::make('vendor_name')
                            ->label('ভেন্ডর / ওয়ার্কশপের নাম'),
                        TextInput::make('vendor_phone')
                            ->label('ভেন্ডর ফোন')
                            ->tel(),
                        TextInput::make('odometer_at_service')
                            ->label('সার্ভিসের সময় ওডোমিটার (KM)')
                            ->numeric()
                            ->required(),
                        DatePicker::make('service_date')
                            ->label('সার্ভিসের তারিখ')
                            ->default(now())
                            ->required(),
                        Textarea::make('service_description')
                            ->label('মেরামতের বিস্তারিত বিবরণ')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('parts_total_cost')
                            ->label('পার্টস খরচ (টাকা)')
                            ->numeric()
                            ->default(0)
                            ->prefix('৳'),
                        TextInput::make('labor_total_cost')
                            ->label('মজুরি খরচ (টাকা)')
                            ->numeric()
                            ->default(0)
                            ->prefix('৳'),
                        TextInput::make('grand_total_cost')
                            ->label('সর্বমোট খরচ (টাকা)')
                            ->numeric()
                            ->default(0)
                            ->prefix('৳'),
                    ])->columns(2),

                Section::make('4. ভেন্ডরে পার্টস প্রেরণের গেট পাস (Vendor Repair Gate Pass)')
                    ->description('যদি মেরামত বা লেদ কাজের জন্য পার্টস ভেন্ডরে বাইরে পাঠাতে হয়')
                    ->schema([
                        Toggle::make('needs_vendor_repair_gatepass')
                            ->label('ভেন্ডরে বাইরে পাঠানোর গেট পাস প্রয়োজন কি না?')
                            ->default(false),
                        Select::make('erp_gatepass_type')
                            ->label(fn () => __('vfms.erp_gatepass_type'))
                            ->options([
                                'RETURNABLE_GATE_PASS' => 'Returnable Gate Pass (ফেরতযোগ্য গেট পাস - RGP)',
                                'NON_RETURNABLE_GATE_PASS' => 'Non-Returnable Gate Pass (অফেরতযোগ্য গেট পাস - NRGP)',
                            ])
                            ->default('RETURNABLE_GATE_PASS'),
                        TextInput::make('erp_gatepass_no')
                            ->label(fn () => __('vfms.erp_gatepass_no'))
                            ->placeholder('e.g. GP-2026-00441'),
                        FileUpload::make('erp_gatepass_copy')
                            ->label(fn () => __('vfms.erp_gatepass_copy'))
                            ->disk('public')
                            ->directory('erp/gatepasses')
                            ->acceptedFileTypes(['image/*', 'application/pdf']),
                        DateTimePicker::make('parts_sent_to_vendor_at')
                            ->label(fn () => __('vfms.parts_sent_to_vendor')),
                        DateTimePicker::make('parts_returned_from_vendor_at')
                            ->label(fn () => __('vfms.parts_returned_from_vendor')),
                    ])->columns(2),

                Section::make('5. সেন্ট্রাল স্টোরে পুরাতন পার্টস জমা ও বিল ক্লিয়ারেন্স (Scrap Surrender & Payment Clearance)')
                    ->description('বিল পেমেন্টের আগে পুরাতন পার্টস স্টোর অফিসারের নিকট জমা দেওয়া বাধ্যতামূলক')
                    ->schema([
                        Toggle::make('requires_old_parts_surrender')
                            ->label('পুরাতন পার্টস স্টোরে জমা দেওয়া আবশ্যক?')
                            ->default(true),
                        Toggle::make('is_old_parts_surrendered')
                            ->label('পুরাতন পার্টস স্টোরে জমা সম্পন্ন হয়েছে?')
                            ->default(false),
                        Select::make('store_acknowledged_by')
                            ->label('স্টোর অফিসার (Store Acknowledged By)')
                            ->relationship('storeAcknowledgedBy', 'name')
                            ->searchable(),
                        DateTimePicker::make('store_acknowledged_at')
                            ->label('স্টোরে জমা গ্রহণের সময়'),
                        Select::make('payment_status')
                            ->label('বিল পেমেন্ট অবস্থা')
                            ->options([
                                'PENDING_ADMIN_APPROVAL' => 'Pending Admin Approval (অনুমোদন বাকি)',
                                'PENDING_PARTS_SURRENDER' => 'Pending Parts Surrender (পুরাতন পার্টস জমা বাকি)',
                                'READY_FOR_PAYMENT' => 'Ready For Payment (বিল পেমেন্টের জন্য প্রস্তুত)',
                                'PAID' => 'Paid (পরিশোধিত)',
                                'CANCELLED' => 'Cancelled (বাতিল)',
                            ])
                            ->default('PENDING_ADMIN_APPROVAL')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
