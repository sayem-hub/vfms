<?php

namespace App\Filament\Resources\MaintenanceRecords\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pre_requisition_no')
                    ->label(fn () => __('vfms.pre_requisition_no'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('admin_approval_status')
                    ->label(fn () => __('vfms.admin_approval_status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'APPROVED_BY_ADMIN' => 'success',
                        'PENDING_ADMIN_APPROVAL' => 'warning',
                        'REJECTED_BY_ADMIN' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('erp_requisition_no')
                    ->label(fn () => __('vfms.erp_requisition_no'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->placeholder('ট্যাগ করা হয়নি (Pending Tag)')
                    ->searchable(),
                TextColumn::make('vehicle.registration_no')
                    ->label(fn () => __('vfms.registration_no'))
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('maintenance_type')
                    ->label(fn () => __('vfms.service_type'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('workshop_type')
                    ->label(fn () => __('vfms.workshop'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('grand_total_cost')
                    ->label('মোট খরচ')
                    ->money('BDT')
                    ->sortable(),
                TextColumn::make('erp_gatepass_no')
                    ->label(fn () => __('vfms.erp_gatepass_no'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->placeholder('প্রযোজ্য নয় (N/A)')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('is_old_parts_surrendered')
                    ->label(fn () => __('vfms.scrap_parts_surrender'))
                    ->badge()
                    ->state(fn ($record) => $record->requires_old_parts_surrender
                        ? ($record->is_old_parts_surrendered ? '✅ স্টোরে জমা সম্পন্ন' : '⚠️ স্টোরে জমা বাকি')
                        : 'প্রযোজ্য নয় (N/A)')
                    ->color(fn ($record) => $record->requires_old_parts_surrender
                        ? ($record->is_old_parts_surrendered ? 'success' : 'danger')
                        : 'gray'),
                TextColumn::make('payment_status')
                    ->label('বিল পেমেন্ট অবস্থা')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'PAID' => 'success',
                        'READY_FOR_PAYMENT' => 'info',
                        'PENDING_PARTS_SURRENDER' => 'danger',
                        'PENDING_ADMIN_APPROVAL' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('service_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('admin_approval_status')
                    ->label('এডমিন অনুমোদন')
                    ->options([
                        'PENDING_ADMIN_APPROVAL' => 'Pending Admin Review',
                        'APPROVED_BY_ADMIN' => 'Approved by Admin',
                        'REJECTED_BY_ADMIN' => 'Rejected by Admin',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('বিল অবস্থা')
                    ->options([
                        'PENDING_ADMIN_APPROVAL' => 'Pending Admin Approval',
                        'PENDING_PARTS_SURRENDER' => 'Pending Parts Surrender',
                        'READY_FOR_PAYMENT' => 'Ready for Payment',
                        'PAID' => 'Paid',
                    ]),
            ])
            ->recordActions([
                // 1. Digital Admin Approval Action
                Action::make('admin_approve')
                    ->label('এডমিন অনুমোদন (Approve)')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn ($record) => $record->admin_approval_status === 'PENDING_ADMIN_APPROVAL')
                    ->form([
                        Textarea::make('admin_remarks')
                            ->label('এডমিন হেডের মন্তব্য (Remarks)')
                            ->default('Approved for central store ERP requisition process.'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'admin_approval_status' => 'APPROVED_BY_ADMIN',
                            'admin_head_id' => auth()->id() ?? 1,
                            'admin_approved_at' => now(),
                            'admin_remarks' => $data['admin_remarks'] ?? null,
                            'status' => 'ADMIN_APPROVED_AWAITING_ERP',
                        ]);

                        Notification::make()
                            ->title('প্রি-রিকুইজিশন অনুমোদিত')
                            ->body('এডমিন হেড ডিজিটাল অনুমোদন সম্পন্ন হয়েছে। সেন্ট্রাল স্টোর এখন ERP-তে রিকুইজিশন করবে।')
                            ->success()
                            ->send();
                    }),

                // 2. Tag ERP Requisition Action (After Central Store generates SRQ / RQSN)
                Action::make('tag_erp_requisition')
                    ->label('ইআরপি রিকুইজিশন ট্যাগ (Tag ERP)')
                    ->icon(Heroicon::OutlinedTag)
                    ->color('info')
                    ->visible(fn ($record) => $record->admin_approval_status === 'APPROVED_BY_ADMIN')
                    ->form([
                        Select::make('erp_requisition_type')
                            ->label('ইআরপি রিকুইজিশনের ধরন')
                            ->options([
                                'SERVICE_REQUISITION' => 'Service Requisition (SRQ - যেমন: NAZBL-SRQ-26-00389)',
                                'PARTS_REQUISITION' => 'Parts / Purchase Requisition (RQSN - যেমন: NAZBL-RQSN-26-02116)',
                            ])
                            ->default($record->erp_requisition_type ?? 'SERVICE_REQUISITION')
                            ->required(),
                        TextInput::make('erp_requisition_no')
                            ->label('ইআরপি রিকুইজিশন নম্বর')
                            ->placeholder('e.g. NAZBL-SRQ-26-00389 or NAZBL-RQSN-26-02116')
                            ->default($record->erp_requisition_no)
                            ->required(),
                        DatePicker::make('erp_requisition_date')
                            ->label('ইআরপি রিকুইজিশনের তারিখ')
                            ->default($record->erp_requisition_date ?? now())
                            ->required(),
                        FileUpload::make('erp_requisition_copy')
                            ->label('ইআরপি রিকুইজিশনের স্ক্যান কপি / ছবি')
                            ->disk('public')
                            ->directory('erp/maintenance_requisitions')
                            ->acceptedFileTypes(['image/*', 'application/pdf']),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'erp_requisition_type' => $data['erp_requisition_type'],
                            'erp_requisition_no' => $data['erp_requisition_no'],
                            'erp_requisition_date' => $data['erp_requisition_date'],
                            'erp_requisition_copy' => $data['erp_requisition_copy'] ?? $record->erp_requisition_copy,
                            'erp_requisition_tagged_at' => now(),
                            'erp_requisition_tagged_by' => auth()->id() ?? 1,
                            'status' => 'ERP_REQ_TAGGED',
                        ]);

                        Notification::make()
                            ->title('ইআরপি রিকুইজিশন সফলভাবে ট্যাগ করা হয়েছে')
                            ->body("ERP No: {$data['erp_requisition_no']} সফলভাবে যুক্ত হয়েছে।")
                            ->success()
                            ->send();
                    }),

                // 3. Central Store Old Parts Surrender Confirmation Action
                Action::make('confirm_scrap')
                    ->label('স্টোরে পার্টস জমা গ্রহণ (Store Receipt)')
                    ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
                    ->color('warning')
                    ->visible(fn ($record) => $record->requires_old_parts_surrender && ! $record->is_old_parts_surrendered)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_old_parts_surrendered' => true,
                            'store_acknowledged_by' => auth()->id() ?? 1,
                            'store_acknowledged_at' => now(),
                            'payment_status' => 'READY_FOR_PAYMENT',
                            'status' => 'COMPLETED',
                        ]);

                        Notification::make()
                            ->title('পুরাতন পার্টস স্টোরে গ্রহণ নিশ্চিত করা হয়েছে')
                            ->body('স্টোর অফিসার পার্টস বুঝে নিয়েছেন। বিল পেমেন্টের জন্য ক্লিয়ার করা হলো।')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
