<x-filament-panels::page>
    @vite(['resources/css/app.css'])

    @php
        $totalCompanies = count($matrix);
        $totalVehicles = array_sum(array_column($matrix, 'vehicles_count'));
        $totalTrips = array_sum(array_column($matrix, 'trips_count'));
        $totalKm = array_sum(array_column($matrix, 'total_km'));
        $totalFuel = array_sum(array_column($matrix, 'fuel_cost'));
        $totalMaint = array_sum(array_column($matrix, 'maintenance_cost'));
        $totalGrand = array_sum(array_column($matrix, 'grand_total'));
    @endphp

    <div class="space-y-6">
        <!-- Executive KPI Banner & Period Selector -->
        <div class="rounded-2xl p-6 text-white shadow-lg bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 border-l-8 border-orange-500">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <span class="inline-block px-3 py-1 bg-orange-600/30 text-orange-200 border border-orange-500/40 rounded-full text-xs font-bold uppercase tracking-wide">
                        🏢 {{ app()->getLocale() === 'bn' ? 'সিস্টার কনসার্ন আন্তঃপ্রতিষ্ঠান খরচ বণ্টন' : 'Inter-Company Central Fleet Chargeback' }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight mt-2 text-white">
                        {{ app()->getLocale() === 'bn' ? 'কেন্দ্রীয় পরিবহন ব্যয় পুনর্বণ্টন ও হিসাব লেজার' : 'Fleet Cost Allocation & Journal Reconciliation' }}
                    </h2>
                    <p class="text-slate-300 text-sm mt-1">
                        {{ app()->getLocale() === 'bn' ? 'প্রতিটি কনসার্নের প্রকৃত কিলোমিটার ও ট্রিপ ব্যবহারের ভিত্তিতে আনুপাতিক ব্যয় বিশ্লেষণ' : 'Proportional operating expense distribution based on logged vehicle usage, trips, and odometer' }}
                    </p>
                </div>

                <!-- Period Selector & Total Chargeable -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-white/10 p-4 rounded-2xl border border-white/10 backdrop-blur-sm">
                    <div>
                        <label class="text-[11px] uppercase font-bold text-slate-300 block mb-1">
                            {{ app()->getLocale() === 'bn' ? 'হিসাব মাস নির্বাচন:' : 'Allocation Period:' }}
                        </label>
                        <input type="month" wire:model.live="selectedPeriod" 
                               class="text-sm font-bold rounded-xl border border-slate-600 bg-slate-900/90 text-white focus:border-orange-500 focus:ring-orange-500 p-2.5" />
                    </div>
                    <div class="sm:text-right sm:border-l sm:border-white/10 sm:pl-4">
                        <span class="text-[11px] text-slate-300 uppercase font-bold block">
                            {{ app()->getLocale() === 'bn' ? 'সর্বমোট চার্জযোগ্য (মাসিক)' : 'Total Chargeable (Month)' }}
                        </span>
                        <span class="text-2xl sm:text-3xl font-black font-mono text-orange-400 mt-0.5 block">
                            ৳ {{ number_format($totalGrand, 0) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Aggregate Metric Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-6 text-center">
                <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'অঙ্গপ্রতিষ্ঠান' : 'Companies' }}</span>
                    <span class="font-mono font-black text-lg text-white mt-1 block">{{ $totalCompanies }}</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট ট্রিপ' : 'Total Trips' }}</span>
                    <span class="font-mono font-black text-lg text-cyan-300 mt-1 block">{{ $totalTrips }}</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট চালিত কিমি' : 'Total KM' }}</span>
                    <span class="font-mono font-black text-lg text-white mt-1 block">{{ number_format($totalKm, 1) }} KM</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'জ্বালানী খরচ' : 'Fuel Cost' }}</span>
                    <span class="font-mono font-black text-lg text-amber-300 mt-1 block">৳ {{ number_format($totalFuel, 0) }}</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মেরামত খরচ' : 'Maintenance' }}</span>
                    <span class="font-mono font-black text-lg text-rose-300 mt-1 block">৳ {{ number_format($totalMaint, 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Section: Live Inter-Company Allocation Matrix Table -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                    <span class="text-xl">🏢</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'অঙ্গপ্রতিষ্ঠান ভিত্তিক আনুপাতিক খরচ বিভাজন ও প্রাক্কলন' : 'Live Company Cost Allocation Matrix' }} ({{ $selectedPeriod }})</span>
                </div>
            </x-slot>
            <x-slot name="description">
                {{ app()->getLocale() === 'bn' ? 'চলতি মাসে প্রতিটি সিস্টার কনসার্নের প্রকৃত জ্বালানী, মেরামত, ও পরিচালন ব্যয় অনুযায়ী জার্নাল প্রস্তুত করুন' : 'Compute real-time expense ratios per sister company and generate binding ledger journals' }}
            </x-slot>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase text-[11px]">
                            <th class="p-3.5">{{ app()->getLocale() === 'bn' ? 'প্রতিষ্ঠান' : 'Company' }}</th>
                            <th class="p-3.5 text-center">{{ app()->getLocale() === 'bn' ? 'গাড়ি' : 'Vehicles' }}</th>
                            <th class="p-3.5 text-center">{{ app()->getLocale() === 'bn' ? 'ট্রিপ' : 'Trips' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'মোট কিমি' : 'Total KM' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'জ্বালানী (৳)' : 'Fuel (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'মেরামত (৳)' : 'Maint (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'টোল ও ট্রিপ (৳)' : 'Tolls (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'ড্রাইভার (৳)' : 'Driver (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'সর্বমোট চার্জযোগ্য (৳)' : 'Chargeable (BDT)' }}</th>
                            <th class="p-3.5 text-center">{{ app()->getLocale() === 'bn' ? 'অ্যাকশন' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                        @forelse($matrix as $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $row['company_name'] }}</div>
                                    <span class="text-[11px] text-slate-500 font-mono font-semibold">{{ $row['company_code'] }}</span>
                                </td>
                                <td class="p-3.5 text-center font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $row['vehicles_count'] }}
                                </td>
                                <td class="p-3.5 text-center font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $row['trips_count'] }}
                                </td>
                                <td class="p-3.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ number_format($row['total_km'], 1) }} KM
                                </td>
                                <td class="p-3.5 text-right font-mono text-amber-700 dark:text-amber-400 font-bold">
                                    ৳ {{ number_format($row['fuel_cost'], 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-rose-700 dark:text-rose-400 font-bold">
                                    ৳ {{ number_format($row['maintenance_cost'], 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-700 dark:text-slate-300">
                                    ৳ {{ number_format($row['operating_cost'], 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-700 dark:text-slate-300">
                                    ৳ {{ number_format($row['driver_cost'], 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono font-black text-slate-900 dark:text-white text-sm">
                                    ৳ {{ number_format($row['grand_total'], 0) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <button type="button" 
                                            wire:click="generateJournal({{ $row['company_id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="generateJournal({{ $row['company_id'] }})"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white rounded-xl font-bold text-xs shadow-sm hover:shadow transition disabled:opacity-50">
                                        <span wire:loading.remove wire:target="generateJournal({{ $row['company_id'] }})">
                                            ⚡ {{ app()->getLocale() === 'bn' ? 'জার্নাল তৈরি' : 'Generate' }}
                                        </span>
                                        <span wire:loading wire:target="generateJournal({{ $row['company_id'] }})">
                                            ⏳ {{ app()->getLocale() === 'bn' ? 'তৈরি হচ্ছে...' : 'Saving...' }}
                                        </span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-8 text-center text-slate-400 text-sm">
                                    {{ app()->getLocale() === 'bn' ? 'কোন অঙ্গপ্রতিষ্ঠান রেকর্ড পাওয়া যায়নি।' : 'No company records found for this period.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- Section: Finalized Allocation Journals Table -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                    <span class="text-xl">📑</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'অনুমোদিত ও চূড়ান্ত জার্নাল ভাউচার সমূহ (Finalized Allocation Journals)' : 'Finalized Inter-Company Ledger Journals' }}</span>
                </div>
            </x-slot>
            <x-slot name="description">
                {{ app()->getLocale() === 'bn' ? 'এই ভাউচারগুলো সিস্টার কনসার্নদের জন্য চূড়ান্ত চার্জব্যাক হিসেবে নথিভুক্ত করা হয়েছে' : 'Binding chargeback records approved and posted to the inter-company subledger' }}
            </x-slot>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase text-[11px]">
                            <th class="p-3.5">{{ app()->getLocale() === 'bn' ? 'জার্নাল রেফারেন্স' : 'Journal Ref' }}</th>
                            <th class="p-3.5">{{ app()->getLocale() === 'bn' ? 'চার্জকৃত প্রতিষ্ঠান' : 'Billed Company' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'মোট কিমি' : 'Total KM' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'ফুয়েল খরচ' : 'Fuel' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'রক্ষণাবেক্ষণ' : 'Maint' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'সর্বমোট বরাদ্দ' : 'Grand Total' }}</th>
                            <th class="p-3.5 text-center">{{ app()->getLocale() === 'bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
                            <th class="p-3.5">{{ app()->getLocale() === 'bn' ? 'অনুমোদনকারী' : 'Approved By' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                        @forelse($finalizedJournals as $j)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                                <td class="p-3.5 font-mono font-bold text-orange-600 dark:text-orange-400">
                                    {{ $j->journal_reference_no }}
                                </td>
                                <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                    {{ $j->company->name }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-800 dark:text-slate-200">
                                    {{ number_format((float) $j->total_km_run, 1) }} KM
                                </td>
                                <td class="p-3.5 text-right font-mono text-amber-700 dark:text-amber-400 font-semibold">
                                    ৳ {{ number_format((float) $j->total_fuel_cost, 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-rose-700 dark:text-rose-400 font-semibold">
                                    ৳ {{ number_format((float) $j->total_maintenance_cost, 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono font-black text-slate-900 dark:text-white text-sm">
                                    ৳ {{ number_format((float) $j->grand_total_allocated, 0) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                        ✓ {{ $j->status }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-600 dark:text-slate-300">
                                    <span class="font-semibold text-slate-900 dark:text-white block">{{ $j->approvedBy?->name ?? 'Admin' }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $j->approved_at?->format('d M Y, h:i A') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 text-sm">
                                    {{ app()->getLocale() === 'bn' ? 'চলতি মাসে কোন চূড়ান্ত জার্নাল রেকর্ড তৈরি করা হয়নি।' : 'No finalized journals created for this period yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
