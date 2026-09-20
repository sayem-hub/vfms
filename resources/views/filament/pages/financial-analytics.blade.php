<x-filament-panels::page>
    @vite(['resources/css/app.css'])

    <div class="space-y-6">
        <!-- Executive KPI Banner -->
        <div class="rounded-2xl p-6 text-white shadow-lg bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 border-l-8 border-orange-500">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="inline-block px-3 py-1 bg-orange-600/30 text-orange-200 border border-orange-500/40 rounded-full text-xs font-bold uppercase tracking-wide">
                        📊 {{ app()->getLocale() === 'bn' ? 'ফ্লিট লাইফটাইম আর্থিক লেজার' : 'Fleet Lifetime Financial Ledger' }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight mt-2 text-white">
                        {{ app()->getLocale() === 'bn' ? 'টোটাল কস্ট অব ওনারশিপ (TCO) ও পরিচালন ব্যয়' : 'Total Cost of Ownership & Operating Spend' }}
                    </h2>
                    <p class="text-slate-300 text-sm mt-1">
                        {{ app()->getLocale() === 'bn' ? 'প্রতি কিলোমিটার খরচ (CPK) বিশ্লেষণ ও মেরামত বনাম প্রতিস্থাপন সুপারিশমালা' : 'Cost Per KM (CPK) analysis, lifetime asset costs, and repair vs replace recommendations' }}
                    </p>
                </div>
                <div class="sm:text-right bg-white/10 px-5 py-3 rounded-2xl border border-white/10 backdrop-blur-sm">
                    <div class="text-xs text-slate-300 uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'ফ্লিট লাইফটাইম টিসিও' : 'Total Fleet Lifetime TCO' }}</div>
                    <div class="text-3xl font-black font-mono text-orange-400 mt-0.5">
                        ৳ {{ number_format($fleetTco['grand_total_fleet_tco'], 0) }}
                    </div>
                </div>
            </div>

            <!-- Metric Summary Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-6 text-center">
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মূলধন ক্রয়মূল্য' : 'Capital Value' }}</span>
                    <span class="font-mono font-black text-lg text-white mt-1 block">৳ {{ number_format($fleetTco['total_capital_value'], 0) }}</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট জ্বালানী ব্যয়' : 'Lifetime Fuel' }}</span>
                    <span class="font-mono font-black text-lg text-amber-300 mt-1 block">৳ {{ number_format($fleetTco['total_lifetime_fuel'], 0) }}</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট মেরামত' : 'Maintenance' }}</span>
                    <span class="font-mono font-black text-lg text-rose-300 mt-1 block">৳ {{ number_format($fleetTco['total_lifetime_maintenance'], 0) }}</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট চালিত কিমি' : 'Total Odometer KM' }}</span>
                    <span class="font-mono font-black text-lg text-cyan-300 mt-1 block">{{ number_format($fleetTco['total_fleet_odometer_km'], 0) }} KM</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'লাইফটাইম গড় CPK' : 'Fleet Lifetime CPK' }}</span>
                    <span class="font-mono font-black text-lg text-emerald-400 mt-1 block">৳ {{ number_format($fleetTco['lifetime_fleet_cpk'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Section: Repair vs Replace Advisor (মেরামত বনাম নতুন গাড়ি ক্রয় সুপারিশ) -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                    <span class="text-xl">🛠️</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'মেরামত বনাম নতুন গাড়ি ক্রয় সুপারিশমালা (Repair vs Replace Advisor)' : 'Repair vs Replacement Advisory Engine' }}</span>
                </div>
            </x-slot>
            <x-slot name="description">
                {{ app()->getLocale() === 'bn' ? 'বিগত ১২ মাসের রক্ষণাবেক্ষণ ব্যয় ও পরিচালন পারফরম্যান্সের অর্থনৈতিক বিশ্লেষণ' : 'Economic evaluation based on trailing 12-month maintenance cost vs capital benchmark' }}
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <!-- Replace Recommended -->
                @forelse($recommendations['recommend_replace'] as $item)
                    <div class="p-5 rounded-2xl border-2 border-red-400 bg-red-50 dark:bg-red-950/40 text-red-950 dark:text-red-200 space-y-2.5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-base">{{ $item['registration_no'] }}</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-red-600 text-white uppercase tracking-wider">Replace</span>
                        </div>
                        <div class="text-xs font-bold text-red-800 dark:text-red-300">{{ $item['official'] }} &bull; {{ $item['vehicle_type'] }}</div>
                        <p class="text-xs font-medium text-red-900 dark:text-red-200 leading-relaxed">{{ $item['reason'] }}</p>
                        <div class="text-xs font-mono font-bold text-red-700 dark:text-red-400 pt-2 border-t border-red-200 dark:border-red-800">
                            {{ app()->getLocale() === 'bn' ? 'বিগত ১২ মাসে ব্যয়:' : '12M Maintenance:' }} ৳ {{ number_format($item['trailing_maintenance'], 0) }}
                        </div>
                    </div>
                @empty
                @endforelse

                <!-- Watchlist -->
                @forelse($recommendations['watchlist'] as $item)
                    <div class="p-5 rounded-2xl border-2 border-amber-400 bg-amber-50 dark:bg-amber-950/40 text-amber-950 dark:text-amber-200 space-y-2.5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-base">{{ $item['registration_no'] }}</span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-amber-600 text-white uppercase tracking-wider">Watchlist</span>
                        </div>
                        <div class="text-xs font-bold text-amber-800 dark:text-amber-300">{{ $item['official'] }} &bull; {{ $item['vehicle_type'] }}</div>
                        <p class="text-xs font-medium text-amber-900 dark:text-amber-200 leading-relaxed">{{ $item['reason'] }}</p>
                        <div class="text-xs font-mono font-bold text-amber-700 dark:text-amber-400 pt-2 border-t border-amber-200 dark:border-amber-800">
                            {{ app()->getLocale() === 'bn' ? 'বিগত ১২ মাসে ব্যয়:' : '12M Maintenance:' }} ৳ {{ number_format($item['trailing_maintenance'], 0) }}
                        </div>
                    </div>
                @empty
                @endforelse

                <!-- Economical summary card -->
                <div class="p-5 rounded-2xl border-2 border-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-200 space-y-2.5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-base text-emerald-900 dark:text-emerald-300">🟢 {{ app()->getLocale() === 'bn' ? 'লাভজনক গাড়ি' : 'Economical Vehicles' }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-600 text-white font-mono">
                            {{ count($recommendations['economical']) }}
                        </span>
                    </div>
                    <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300 leading-relaxed">
                        {{ app()->getLocale() === 'bn' ? 'এই গাড়িগুলোর রক্ষণাবেক্ষণ ব্যয় বাজেট সীমার মধ্যে রয়েছে এবং এগুলো স্বাভাবিক পরিচালনে অব্যাহত রাখা লাভজনক।' : 'These vehicles are operating within normal maintenance budgets and are economical to retain.' }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        <!-- Section: Vehicle Lifetime Total Cost of Ownership (TCO) Ledger Table -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                    <span>📖</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'যানবাহন ভিত্তিক লাইফটাইম টিসিও (TCO) ও CPK লেজার' : 'Vehicle Lifetime TCO & Cost Per KM Ledger' }}</span>
                </div>
            </x-slot>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase text-xs">
                            <th class="p-3.5">{{ app()->getLocale() === 'bn' ? 'গাড়ির নম্বর' : 'Vehicle No' }}</th>
                            <th class="p-3.5">{{ app()->getLocale() === 'bn' ? 'ব্যবহারকারী / রুট' : 'Official / Route' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'ক্রয়মূল্য (৳)' : 'Purchase (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'মোট ফুয়েল (৳)' : 'Fuel (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'মোট মেরামত (৳)' : 'Maint (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'মোট টিসিও (৳)' : 'Total TCO (BDT)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'বর্তমান ওডোমিটার' : 'Odometer (KM)' }}</th>
                            <th class="p-3.5 text-right">{{ app()->getLocale() === 'bn' ? 'লাইফটাইম CPK' : 'Lifetime CPK' }}</th>
                            <th class="p-3.5 text-center">{{ app()->getLocale() === 'bn' ? 'অর্থনৈতিক সুপারিশ' : 'Advisory' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                        @foreach($vehicleTcoList as $item)
                            @php
                                $v = $item['vehicle'];
                                $adv = $item['advisory'];
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                                <td class="p-3.5 font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $v->registration_no }}
                                    <span class="block text-xs text-slate-500 font-normal">{{ $v->brand }} {{ $v->model_name }}</span>
                                </td>
                                <td class="p-3.5 text-slate-700 dark:text-slate-300">
                                    {{ $v->dedicated_to_official ?: ($v->fixedRoutes->first()?->route_name ?? $v->vehicle_type) }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-800 dark:text-slate-200">
                                    {{ $item['purchase_price'] > 0 ? number_format($item['purchase_price'], 0) : '-' }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-orange-700 dark:text-orange-400 font-bold">
                                    ৳ {{ number_format($item['total_fuel_cost'], 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-red-700 dark:text-red-400 font-bold">
                                    ৳ {{ number_format($item['total_maintenance_cost'], 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono font-black text-slate-900 dark:text-white">
                                    ৳ {{ number_format($item['grand_total_tco'], 0) }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-700 dark:text-slate-300">
                                    {{ number_format($v->current_odometer, 0) }} KM
                                </td>
                                <td class="p-3.5 text-right font-mono font-black text-emerald-700 dark:text-emerald-400 text-sm">
                                    ৳ {{ number_format($item['lifetime_cpk'], 2) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-black {{ $adv['status'] === 'RECOMMEND_REPLACE' ? 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200' : ($adv['status'] === 'WATCHLIST' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200') }}">
                                        {{ $adv['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
