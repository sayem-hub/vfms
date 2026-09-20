<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top Period Selector Strip -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider block">
                    {{ app()->getLocale() === 'bn' ? 'অ্যালোকেশন সময়কাল নির্বাচন' : 'Cost Allocation Period' }}
                </span>
                <h3 class="text-lg font-bold text-slate-900 mt-0.5">
                    {{ app()->getLocale() === 'bn' ? 'সিস্টার কনসার্ন ভিত্তিক কেন্দ্রীয় পরিবহন খরচ চার্জব্যাক' : 'Inter-Company Central Fleet Cost Chargeback' }}
                </h3>
            </div>
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-700 uppercase">{{ app()->getLocale() === 'bn' ? 'মাস:' : 'Month:' }}</label>
                <input type="month" wire:model.live="selectedPeriod" 
                       class="text-sm font-semibold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>
        </div>

        <!-- Live Inter-Company Allocation Matrix Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                    <span>🏢</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'অঙ্গপ্রতিষ্ঠান ভিত্তিক আনুপাতিক খরচ বিভাজন' : 'Live Company Cost Breakdown Matrix' }} ({{ $selectedPeriod }})</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold uppercase text-[10px]">
                            <th class="p-3">{{ app()->getLocale() === 'bn' ? 'প্রতিষ্ঠান' : 'Company' }}</th>
                            <th class="p-3 text-center">{{ app()->getLocale() === 'bn' ? 'গাড়ি সংখ্যা' : 'Vehicles' }}</th>
                            <th class="p-3 text-center">{{ app()->getLocale() === 'bn' ? 'ট্রিপ সংখ্যা' : 'Trips' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'মোট কিমি' : 'Total KM' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'জ্বালানী (৳)' : 'Fuel (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'রক্ষণাবেক্ষণ (৳)' : 'Maint (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'টোল ও ট্রিপ (৳)' : 'Tolls (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'ড্রাইভার বেতন (৳)' : 'Driver (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'সর্বমোট চার্জযোগ্য (৳)' : 'Chargeable (BDT)' }}</th>
                            <th class="p-3 text-center">{{ app()->getLocale() === 'bn' ? 'অ্যাকশন' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($matrix as $row)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">{{ $row['company_name'] }}</div>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $row['company_code'] }}</span>
                                </td>
                                <td class="p-3 text-center font-mono font-semibold text-slate-700">
                                    {{ $row['vehicles_count'] }}
                                </td>
                                <td class="p-3 text-center font-mono font-semibold text-slate-700">
                                    {{ $row['trips_count'] }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-slate-800">
                                    {{ number_format($row['total_km'], 1) }} KM
                                </td>
                                <td class="p-3 text-right font-mono text-orange-700">
                                    {{ number_format($row['fuel_cost'], 0) }}
                                </td>
                                <td class="p-3 text-right font-mono text-red-700">
                                    {{ number_format($row['maintenance_cost'], 0) }}
                                </td>
                                <td class="p-3 text-right font-mono text-slate-700">
                                    {{ number_format($row['operating_cost'], 0) }}
                                </td>
                                <td class="p-3 text-right font-mono text-slate-700">
                                    {{ number_format($row['driver_cost'], 0) }}
                                </td>
                                <td class="p-3 text-right font-mono font-black text-slate-900 text-sm">
                                    ৳ {{ number_format($row['grand_total'], 0) }}
                                </td>
                                <td class="p-3 text-center">
                                    <button type="button" wire:click="generateJournal({{ $row['company_id'] }})"
                                            class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-bold text-[11px] shadow-sm transition">
                                        {{ app()->getLocale() === 'bn' ? 'জার্নাল তৈরি করুন' : 'Generate Journal' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-6 text-center text-slate-400 text-xs">
                                    {{ app()->getLocale() === 'bn' ? 'কোন কোম্পানি রেকর্ড পাওয়া যায়নি।' : 'No company records found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Finalized Allocation Journals Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                    <span>📑</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'অনুমোদিত ও চূড়ান্ত জার্নাল ভাউচার সমূহ (Finalized Allocation Journals)' : 'Finalized Inter-Company Journals' }}</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold uppercase text-[10px]">
                            <th class="p-3">{{ app()->getLocale() === 'bn' ? 'জার্নাল রেফারেন্স নং' : 'Journal Ref No' }}</th>
                            <th class="p-3">{{ app()->getLocale() === 'bn' ? 'চার্জকৃত প্রতিষ্ঠান' : 'Billed Company' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'মোট কিমি' : 'Total KM' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'ফুয়েল খরচ' : 'Fuel' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'রক্ষণাবেক্ষণ' : 'Maint' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'সর্বমোট বরাদ্দ' : 'Grand Total' }}</th>
                            <th class="p-3 text-center">{{ app()->getLocale() === 'bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
                            <th class="p-3">{{ app()->getLocale() === 'bn' ? 'অনুমোদনকারী' : 'Approved By' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($finalizedJournals as $j)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-mono font-bold text-orange-700">
                                    {{ $j->journal_reference_no }}
                                </td>
                                <td class="p-3 font-bold text-slate-900">
                                    {{ $j->company->name }}
                                </td>
                                <td class="p-3 text-right font-mono text-slate-800">
                                    {{ number_format((float) $j->total_km_run, 1) }} KM
                                </td>
                                <td class="p-3 text-right font-mono text-orange-700">
                                    ৳ {{ number_format((float) $j->total_fuel_cost, 0) }}
                                </td>
                                <td class="p-3 text-right font-mono text-red-700">
                                    ৳ {{ number_format((float) $j->total_maintenance_cost, 0) }}
                                </td>
                                <td class="p-3 text-right font-mono font-black text-slate-900 text-sm">
                                    ৳ {{ number_format((float) $j->grand_total_allocated, 0) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        {{ $j->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-600">
                                    {{ $j->approvedBy?->name ?? 'Admin' }}
                                    <span class="block text-[10px] text-slate-400">{{ $j->approved_at?->format('d M Y, h:i A') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-400 text-xs">
                                    {{ app()->getLocale() === 'bn' ? 'চলতি মাসে কোন চূড়ান্ত জার্নাল রেকর্ড তৈরি করা হয়নি।' : 'No finalized journals for this period yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
