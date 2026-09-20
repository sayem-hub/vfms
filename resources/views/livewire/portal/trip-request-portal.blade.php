<div class="space-y-8">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 rounded-2xl p-6 text-white shadow-md border-l-4 border-orange-600">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="mb-2">
                    <span class="inline-block px-2.5 py-0.5 bg-orange-600/30 text-orange-200 border border-orange-500/40 rounded-full text-[11px] font-bold tracking-wide uppercase">
                        {{ app()->getLocale() === 'bn' ? 'ফ্যাক্টরি ও অফিস পরিবহন' : 'Factory & Transport Operations' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                    {{ app()->getLocale() === 'bn' ? 'গাড়ি ও ট্রিপ রিকোয়েস্ট পোর্টাল' : 'Vehicle & Trip Request Portal' }}
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    {{ app()->getLocale() === 'bn' ? 'কর্মকর্তা, কর্মচারী ও রপ্তানি পণ্য পরিবহনের জন্য অভ্যন্তরীণ ট্রিপ রিকোয়েস্ট দাখিল করুন' : 'Submit internal vehicle requests for official duty, staff commute, or export shipments' }}
                </p>
            </div>
            <div>
                <a href="#recent-requests" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
                    📋 {{ app()->getLocale() === 'bn' ? 'আমার রিকোয়েস্ট সমূহ' : 'My Requests' }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Grid: Form & Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Trip Request Form (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <span>📝</span>
                <span>{{ app()->getLocale() === 'bn' ? 'নতুন ট্রিপ রিকোয়েস্ট ফরম' : 'New Trip Request Form' }}</span>
            </h2>

            <!-- Quick Preset Route Buttons -->
            <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl p-3">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-2">
                    ⚡ {{ app()->getLocale() === 'bn' ? 'সচরাচর রুট নির্বাচন (Quick Route Presets):' : 'Frequent Route Presets:' }}
                </span>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="setPresetRoute('HO_TO_GZP')"
                            class="text-xs px-3 py-1.5 bg-white border border-slate-300 hover:border-orange-500 hover:text-orange-700 rounded-lg font-medium shadow-2xs transition">
                        📍 বারিধারা HO ➔ বিকে বাড়ি কারখানা
                    </button>
                    <button type="button" wire:click="setPresetRoute('GZP_TO_CGZP')"
                            class="text-xs px-3 py-1.5 bg-white border border-slate-300 hover:border-orange-500 hover:text-orange-700 rounded-lg font-medium shadow-2xs transition">
                        🚢 বিকে বাড়ি কারখানা ➔ ভবানীপুর
                    </button>
                    <button type="button" wire:click="setPresetRoute('HO_TO_CGZP')"
                            class="text-xs px-3 py-1.5 bg-white border border-slate-300 hover:border-orange-500 hover:text-orange-700 rounded-lg font-medium shadow-2xs transition">
                        🏭 বারিধারা HO ➔ ভবানীপুর
                    </button>
                    <button type="button" wire:click="setPresetRoute('HO_TO_AGZP')"
                            class="text-xs px-3 py-1.5 bg-white border border-slate-300 hover:border-orange-500 hover:text-orange-700 rounded-lg font-medium shadow-2xs transition">
                        🏭 বারিধারা HO ➔ বিআইডিসি রোড
                    </button>
                    <button type="button" wire:click="setPresetRoute('GZP_TO_HO')"
                            class="text-xs px-3 py-1.5 bg-white border border-slate-300 hover:border-orange-500 hover:text-orange-700 rounded-lg font-medium shadow-2xs transition">
                        🚢 বিকে বাড়ি কারখানা ➔ বারিধারা HO
                    </button>
                    <button type="button" wire:click="setPresetRoute('CGZP_TO_HO')"
                            class="text-xs px-3 py-1.5 bg-white border border-slate-300 hover:border-orange-500 hover:text-orange-700 rounded-lg font-medium shadow-2xs transition">
                        🚢 ভবানীপুর ➔ বারিধারা HO
                    </button>
                </div>
            </div>

            <form wire:submit="submitRequest" class="space-y-5">
                <!-- Company & Factory Unit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            {{ app()->getLocale() === 'bn' ? 'কোম্পানি' : 'Company' }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="company_id" class="w-full text-sm rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->code }})</option>
                            @endforeach
                        </select>
                        @error('company_id') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            {{ app()->getLocale() === 'bn' ? 'ফ্যাক্টরি / ইউনিট' : 'Factory Unit' }}
                        </label>
                        <select wire:model="factory_unit_id" class="w-full text-sm rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white">
                            <option value="">-- {{ app()->getLocale() === 'bn' ? 'ইউনিট নির্বাচন করুন' : 'Select Unit' }} --</option>
                            @foreach($factoryUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->location_code }})</option>
                            @endforeach
                        </select>
                        @error('factory_unit_id') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Trip Type & Scheduled Start -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            {{ app()->getLocale() === 'bn' ? 'ট্রিপের ধরন' : 'Trip Type' }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="trip_type" class="w-full text-sm rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white">
                            <option value="OFFICIAL_DUTY">Official Duty (অফিসিয়াল দায়িত্ব)</option>
                            <option value="EXPORT_SHIPMENT">Covered Van Export Shipment (রপ্তানি চালান)</option>
                            <option value="EMPLOYEE_COMMUTE">Employee Commute (কর্মচারী যাতায়াত)</option>
                            <option value="EMERGENCY_AMBULANCE">Emergency Ambulance (জরুরি এ্যাম্বুলেন্স)</option>
                            <option value="GUEST_QC_PICKUP">Buyer / QC / Auditor Pickup (বায়ার/অডিটর)</option>
                            <option value="PERSONAL_USE">Personal Use (ব্যক্তিগত)</option>
                        </select>
                        @error('trip_type') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            {{ app()->getLocale() === 'bn' ? 'যাত্রার সময়' : 'Departure Time' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" wire:model="scheduled_start_time"
                               class="w-full text-sm rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                        @error('scheduled_start_time') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Origin & Destination -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            {{ app()->getLocale() === 'bn' ? 'প্রস্থানস্থল (Origin)' : 'Origin Location' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="origin_name" placeholder="e.g. BK Bari, NAZ Bangladesh Ltd"
                               class="w-full text-sm rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                        @error('origin_name') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            {{ app()->getLocale() === 'bn' ? 'গন্তব্যস্থল (Destination)' : 'Destination Location' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="destination_name" placeholder="e.g. Corporate Head Office, Baridhara DOHS"
                               class="w-full text-sm rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                        @error('destination_name') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Purpose -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        {{ app()->getLocale() === 'bn' ? 'যাত্রার উদ্দেশ্য / বিবরণ' : 'Purpose & Details' }} <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="purpose" rows="3" placeholder="{{ app()->getLocale() === 'bn' ? 'যাত্রার বিস্তারিত কারণ লিখুন...' : 'Enter trip purpose and details...' }}"
                              class="w-full text-sm rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white"></textarea>
                    @error('purpose') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-2 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-sm shadow-md shadow-orange-200 transition flex items-center gap-2">
                        <span wire:loading.remove wire:target="submitRequest">
                            ✓ {{ app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট জমা দিন' : 'Submit Trip Request' }}
                        </span>
                        <span wire:loading wire:target="submitRequest">
                            ⏳ Processing Route & Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Side Panel (1 col) -->
        <div class="space-y-6">
            <!-- Instructions Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 text-sm mb-3 flex items-center gap-2">
                    <span>ℹ️</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট নির্দেশিকা' : 'Trip Request Guidelines' }}</span>
                </h3>
                <ul class="text-xs text-slate-600 space-y-2 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="text-orange-600 font-bold">•</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'যাত্রার অন্তত ২ ঘণ্টা পূর্বে রিকোয়েস্ট সাবমিট করুন।' : 'Submit request at least 2 hours prior to scheduled departure.' }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-orange-600 font-bold">•</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'সিস্টেম স্বয়ংক্রিয়ভাবে ম্যাপ থেকে স্ট্যান্ডার্ড দূরত্ব হিসাব করে রাখবে।' : 'The system automatically pre-calculates the expected highway distance.' }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-orange-600 font-bold">•</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'ট্রিপ অনুমোদিত হলে সিকিউরিটি গেট থেকে গাড়ি ছাড়পত্র পাবে।' : 'Gate pass will be issued by Security Gate once trip is approved.' }}</span>
                    </li>
                </ul>
            </div>

            <!-- Emergency Ambulance Quick Dispatch Button -->
            <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🚑</span>
                    <div>
                        <h4 class="text-sm font-bold text-red-800">{{ app()->getLocale() === 'bn' ? 'জরুরি এ্যাম্বুলেন্স কল' : 'Emergency Ambulance' }}</h4>
                        <p class="text-xs text-red-600">{{ app()->getLocale() === 'bn' ? 'ফ্যাক্টরি মেডিকেল ইমার্জেন্সি' : 'Direct Factory Medical Hotlines' }}</p>
                    </div>
                </div>
                <div class="mt-3 text-xs font-semibold text-red-900 bg-white p-2 rounded-lg border border-red-200 text-center">
                    📞 Medical Incharge: +8801711-998877
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Trip Requests Table -->
    <div id="recent-requests" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <span>📋</span>
                <span>{{ app()->getLocale() === 'bn' ? 'সাম্প্রতিক ট্রিপ রিকোয়েস্ট সমূহ' : 'Recent Trip Requests' }}</span>
            </h3>
            <span class="text-xs text-slate-500 font-medium">Auto-refreshed</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600">
                        <th class="py-3 px-4 font-bold">{{ app()->getLocale() === 'bn' ? 'রিকোয়েস্ট নং' : 'Request No' }}</th>
                        <th class="py-3 px-4 font-bold">{{ app()->getLocale() === 'bn' ? 'গাড়ি ও চালক' : 'Vehicle & Driver' }}</th>
                        <th class="py-3 px-4 font-bold">{{ app()->getLocale() === 'bn' ? 'রুট (প্রস্থান ➔ গন্তব্য)' : 'Route' }}</th>
                        <th class="py-3 px-4 font-bold">{{ app()->getLocale() === 'bn' ? 'যাত্রার সময়' : 'Departure' }}</th>
                        <th class="py-3 px-4 font-bold">{{ app()->getLocale() === 'bn' ? 'ম্যাপ দূরত্ব' : 'Expected KM' }}</th>
                        <th class="py-3 px-4 font-bold">{{ app()->getLocale() === 'bn' ? 'অবস্থা' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentRequests as $req)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-bold text-orange-700 font-mono">
                                {{ $req->request_no }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800">{{ $req->vehicle->registration_no ?? 'Pending Assignment' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $req->driver->name ?? 'Driver Pending' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium text-slate-700">{{ $req->origin_name }}</span>
                                <span class="text-slate-400 mx-1">➔</span>
                                <span class="font-medium text-slate-700">{{ $req->destination_name }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $req->scheduled_start_time ? $req->scheduled_start_time->format('d M, h:i A') : 'N/A' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($req->expected_distance_km)
                                    <span class="font-semibold text-slate-800">{{ $req->expected_distance_km }} KM</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $statusClasses = [
                                        'SUBMITTED' => 'bg-amber-100 text-amber-800',
                                        'HOD_APPROVED' => 'bg-blue-100 text-blue-800',
                                        'DISPATCHED' => 'bg-indigo-100 text-indigo-800',
                                        'GATE_OUT' => 'bg-purple-100 text-purple-800',
                                        'IN_TRIP' => 'bg-teal-100 text-teal-800',
                                        'COMPLETED' => 'bg-emerald-100 text-emerald-800',
                                        'CANCELLED' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusClasses[$req->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">
                                {{ app()->getLocale() === 'bn' ? 'কোন ট্রিপ রিকোয়েস্ট পাওয়া যায়নি' : 'No trip requests submitted yet' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Success Modal -->
    @if($showSuccessModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 text-center shadow-xl">
                <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto text-2xl mb-4 font-bold">
                    ✓
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">
                    {{ app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট সফলভাবে দাখিল হয়েছে!' : 'Trip Request Submitted Successfully!' }}
                </h3>
                <p class="text-xs text-slate-500 mb-4">
                    {{ app()->getLocale() === 'bn' ? 'আপনার রিকোয়েস্ট ট্র্যাকিং নম্বর:' : 'Tracking Request Number:' }}
                </p>
                <div class="bg-slate-100 border border-slate-200 rounded-xl py-3 px-4 text-orange-700 font-mono font-bold text-base mb-6">
                    {{ $createdRequestNo }}
                </div>
                <button type="button" wire:click="$set('showSuccessModal', false)"
                        class="w-full py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-sm font-bold transition">
                    {{ app()->getLocale() === 'bn' ? 'ঠিক আছে (Close)' : 'OK, Close' }}
                </button>
            </div>
        </div>
    @endif
</div>
