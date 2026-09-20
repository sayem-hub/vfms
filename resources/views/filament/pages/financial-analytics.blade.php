<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Executive KPI Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 rounded-2xl p-6 text-white shadow-sm border-l-4 border-orange-600">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="inline-block px-2.5 py-0.5 bg-orange-600/30 text-orange-200 border border-orange-500/40 rounded-full text-[11px] font-bold uppercase tracking-wide">
                        📊 {{ app()->getLocale() === 'bn' ? 'ফ্লিট লাইফটাইম আর্থিক লেজার' : 'Fleet Lifetime Financial Ledger' }}
                    </span>
                    <h2 class="text-2xl font-black tracking-tight mt-1 text-white">
                        {{ app()->getLocale() === 'bn' ? 'টোটাল কস্ট অব ওনারশিপ (TCO) ও পরিচালন ব্যয়' : 'Total Cost of Ownership & Operating Spend' }}
                    </h2>
                    <p class="text-slate-300 text-xs mt-1">
                        {{ app()->getLocale() === 'bn' ? 'প্রতি কিলোমিটার খরচ (CPK) বিশ্লেষণ ও মেরামত বনাম প্রতিস্থাপন সুপারিশমালা' : 'Cost Per KM (CPK) analysis, lifetime asset costs, and repair vs replace recommendations' }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-400 uppercase font-semibold">{{ app()->getLocale() === 'bn' ? 'ফ্লিট লাইফটাইম টিসিও' : 'Total Fleet Lifetime TCO' }}</div>
                    <div class="text-2xl font-bold font-mono text-orange-400">
                        ৳ {{ number_format($fleetTco['grand_total_fleet_tco'], 0) }}
                    </div>
                </div>
            </div>

            <!-- Metric Summary Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-6 text-center text-xs">
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'যানবাহন মূলধন মূল্য' : 'Capital Value' }}</span>
                    <span class="font-mono font-bold text-base text-white">৳ {{ number_format($fleetTco['total_capital_value'], 0) }}</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট জ্বালানী ব্যয়' : 'Lifetime Fuel' }}</span>
                    <span class="font-mono font-bold text-base text-orange-300">৳ {{ number_format($fleetTco['total_lifetime_fuel'], 0) }}</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট রক্ষণাবেক্ষণ' : 'Maintenance' }}</span>
                    <span class="font-mono font-bold text-base text-red-300">৳ {{ number_format($fleetTco['total_lifetime_maintenance'], 0) }}</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মোট চালিত কিমি' : 'Total Odometer KM' }}</span>
                    <span class="font-mono font-bold text-base text-cyan-300">{{ number_format($fleetTco['total_fleet_odometer_km'], 0) }} KM</span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'লাইফটাইম গড় CPK' : 'Fleet Lifetime CPK' }}</span>
                    <span class="font-mono font-bold text-base text-emerald-400">৳ {{ number_format($fleetTco['lifetime_fleet_cpk'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Section: Repair vs Replace Advisor (মেরামত বনাম নতুন গাড়ি ক্রয় সুপারিশ) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛠️</span>
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            {{ app()->getLocale() === 'bn' ? 'মেরামত বনাম নতুন গাড়ি ক্রয় সুপারিশমালা (Repair vs Replace Advisor)' : 'Repair vs Replacement Advisory Engine' }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ app()->getLocale() === 'bn' ? 'বিগত ১২ মাসের রক্ষণাবেক্ষণ ব্যয় ও পরিচালন পারফরম্যান্সের অর্থনৈতিক বিশ্লেষণ' : 'Economic evaluation based on trailing 12-month maintenance cost vs capital benchmark' }}
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $recommendations['needs_attention_count'] > 0 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-emerald-100 text-emerald-900 border border-emerald-300' }}">
                    {{ $recommendations['needs_attention_count'] > 0 ? $recommendations['needs_attention_count'] . ' Vehicles Need Attention' : 'All Vehicles Economical' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Replace Recommended -->
                @forelse($recommendations['recommend_replace'] as $item)
                    <div class="p-4 rounded-xl border border-red-300 bg-red-50 text-red-950 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-sm">{{ $item['registration_no'] }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-600 text-white uppercase">Replace</span>
                        </div>
                        <div class="text-xs font-semibold">{{ $item['official'] }} &bull; {{ $item['vehicle_type'] }}</div>
                        <p class="text-xs text-red-900 leading-relaxed">{{ $item['reason'] }}</p>
                        <div class="text-[11px] font-mono text-red-700 pt-1 border-t border-red-200">
                            {{ app()->getLocale() === 'bn' ? 'বিগত ১২ মাসে ব্যয়:' : '12M Maintenance:' }} ৳ {{ number_format($item['trailing_maintenance'], 0) }}
                        </div>
                    </div>
                @empty
                @endforelse

                <!-- Watchlist -->
                @forelse($recommendations['watchlist'] as $item)
                    <div class="p-4 rounded-xl border border-amber-300 bg-amber-50 text-amber-950 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-sm">{{ $item['registration_no'] }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-600 text-white uppercase">Watchlist</span>
                        </div>
                        <div class="text-xs font-semibold">{{ $item['official'] }} &bull; {{ $item['vehicle_type'] }}</div>
                        <p class="text-xs text-amber-900 leading-relaxed">{{ $item['reason'] }}</p>
                        <div class="text-[11px] font-mono text-amber-700 pt-1 border-t border-amber-200">
                            {{ app()->getLocale() === 'bn' ? 'বিগত ১২ মাসে ব্যয়:' : '12M Maintenance:' }} ৳ {{ number_format($item['trailing_maintenance'], 0) }}
                        </div>
                    </div>
                @empty
                @endforelse

                <!-- Economical summary card -->
                <div class="p-4 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-950 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm">🟢 {{ app()->getLocale() === 'bn' ? 'লাভজনক গাড়ি' : 'Economical Vehicles' }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white font-mono">
                            {{ count($recommendations['economical']) }}
                        </span>
                    </div>
                    <p class="text-xs text-emerald-800 leading-relaxed">
                        {{ app()->getLocale() === 'bn' ? 'এই গাড়িগুলোর রক্ষণাবেক্ষণ ব্যয় বাজেট সীমার মধ্যে রয়েছে এবং এগুলো স্বাভাবিক পরিচালনে অব্যাহত রাখা লাভজনক।' : 'These vehicles are operating within normal maintenance budgets and are economical to retain.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Section: Vehicle Lifetime Total Cost of Ownership (TCO) Ledger Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                    <span>📖</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'যানবাহন ভিত্তিক লাইফটাইম টিসিও (TCO) ও CPK লেজার' : 'Vehicle Lifetime TCO & Cost Per KM Ledger' }}</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold uppercase text-[10px]">
                            <th class="p-3">{{ app()->getLocale() === 'bn' ? 'গাড়ির নম্বর' : 'Vehicle No' }}</th>
                            <th class="p-3">{{ app()->getLocale() === 'bn' ? 'ব্যবহারকারী / রুট' : 'Official / Route' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'ক্রয়মূল্য (৳)' : 'Purchase (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'মোট ফুয়েল (৳)' : 'Fuel (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'মোট মেরামত (৳)' : 'Maint (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'মোট টিসিও (৳)' : 'Total TCO (BDT)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'বর্তমান ওডোমিটার' : 'Odometer (KM)' }}</th>
                            <th class="p-3 text-right">{{ app()->getLocale() === 'bn' ? 'লাইফটাইম CPK' : 'Lifetime CPK' }}</th>
                            <th class="p-3 text-center">{{ app()->getLocale() === 'bn' ? 'অর্থনৈতিক সুপারিশ' : 'Advisory' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vehicleTcoList as $item)
                            @php
                                $v = $item['vehicle'];
                                $adv = $item['advisory'];
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-mono font-bold text-slate-900">
                                    {{ $v->registration_no }}
                                    <span class="block text-[10px] text-slate-500 font-normal">{{ $v->brand }} {{ $v->model_name }}</span>
                                </td>
                                <td class="p-3 text-slate-700">
                                    {{ $v->dedicated_to_official ?: ($v->fixedRoutes->first()?->route_name ?? $v->vehicle_type) }}
                                </td>
                                <td class="p-3 text-right font-mono text-slate-800">
                                    {{ $item['purchase_price'] > 0 ? number_format($item['purchase_price'], 0) : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono text-orange-700 font-semibold">
                                    {{ number_format($item['total_fuel_cost'], 0) }}
                                </td>
                                <td class="p-3 text-right font-mono text-red-700 font-semibold">
                                    {{ number_format($item['total_maintenance_cost'], 0) }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-slate-900">
                                    {{ number_format($item['grand_total_tco'], 0) }}
                                </td>
                                <td class="p-3 text-right font-mono text-slate-700">
                                    {{ number_format($v->current_odometer, 0) }} KM
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-emerald-700 text-sm">
                                    ৳ {{ number_format($item['lifetime_cpk'], 2) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $adv['status'] === 'RECOMMEND_REPLACE' ? 'bg-red-100 text-red-800' : ($adv['status'] === 'WATCHLIST' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        {{ $adv['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
