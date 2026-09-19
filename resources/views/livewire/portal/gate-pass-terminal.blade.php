<div class="space-y-8">
    <!-- Gate Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 rounded-2xl p-6 text-white shadow-md border-l-4 border-orange-600">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="mb-2">
                    <span class="inline-block px-2.5 py-0.5 bg-orange-600/30 text-orange-200 border border-orange-500/40 rounded-full text-[11px] font-bold uppercase tracking-wide">
                        🛡️ {{ app()->getLocale() === 'bn' ? 'ফ্যাক্টরি নিরাপত্তা চেকপোস্ট' : 'Factory Security Checkpoint' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                    {{ app()->getLocale() === 'bn' ? 'সিকিউরিটি গেট পাস টার্মিনাল' : 'Security Gate-Pass Terminal' }}
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    {{ app()->getLocale() === 'bn' ? 'গাড়ির প্রস্থান ও আগমনের ওডোমিটার ও সময় নিশ্চিতকরণ' : 'Record Gate-Out / Gate-In odometers and verify route distance integrity' }}
                </p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-mono font-bold text-orange-400">
                    {{ now()->format('h:i A') }}
                </div>
                <div class="text-xs text-slate-400 font-medium">
                    {{ now()->format('l, d F Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Feedback Notification -->
    @if($feedbackMessage)
        <div class="rounded-2xl p-5 border shadow-sm transition {{ $feedbackType === 'warning' ? 'bg-red-50 border-red-300 text-red-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900' }}">
            <div class="flex items-start gap-3">
                <span class="text-2xl mt-0.5">{{ $feedbackType === 'warning' ? '⚠️' : '✅' }}</span>
                <div>
                    <h4 class="font-bold text-base mb-1">
                        {{ $feedbackType === 'warning' ? (app()->getLocale() === 'bn' ? 'দূরত্ব অডিট সতর্কতা (Distance Alert)' : 'Distance Audit Warning') : (app()->getLocale() === 'bn' ? 'সফল হয়েছে (Success)' : 'Success') }}
                    </h4>
                    <p class="text-sm leading-relaxed">{{ $feedbackMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Terminal Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Search & Active Trips Queue (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center justify-between">
                <span>🔍 {{ app()->getLocale() === 'bn' ? 'গাড়ি ও ট্রিপ অনুসন্ধান' : 'Find Vehicle / Gate Pass' }}</span>
            </h2>

            <!-- Search input -->
            <div>
                <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                       placeholder="{{ app()->getLocale() === 'bn' ? 'গাড়ির নং / গেট পাস নং / ড্রাইভার নাম...' : 'Vehicle No / Gate Pass / Driver...' }}"
                       class="w-full text-sm rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-3 border bg-slate-50 font-medium" />
            </div>

            <!-- Active Trips Queue List -->
            <div class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">
                    {{ app()->getLocale() === 'bn' ? 'চলমান ও অনুমোদিত ট্রিপ সমূহ:' : 'Active / Approved Trips:' }}
                </span>

                @forelse($activeGateTrips as $trip)
                    <button type="button" wire:click="selectTrip({{ $trip->id }})"
                            class="w-full text-left p-3 rounded-xl border transition {{ $selectedTripId === $trip->id ? 'bg-orange-50 border-orange-400 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 border-slate-200' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-slate-900">{{ $trip->vehicle->registration_no ?? 'Unassigned' }}</span>
                            @php
                                $badgeColor = match($trip->status) {
                                    'GATE_OUT', 'IN_TRIP' => 'bg-purple-100 text-purple-800',
                                    'HOD_APPROVED', 'DISPATCHED' => 'bg-blue-100 text-blue-800',
                                    'COMPLETED' => 'bg-emerald-100 text-emerald-800',
                                    default => 'bg-slate-100 text-slate-700'
                                };
                            @endphp
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $badgeColor }}">
                                {{ $trip->status }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-600 mt-1 flex items-center justify-between">
                            <span>👨‍✈️ {{ $trip->driver->name ?? 'No Driver' }}</span>
                            <span class="font-mono text-[10px] text-slate-500">{{ $trip->request_no }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 truncate mt-1">
                            📍 {{ $trip->origin_name }} ➔ {{ $trip->destination_name }}
                        </div>
                    </button>
                @empty
                    <div class="text-center py-6 text-xs text-slate-400">
                        {{ app()->getLocale() === 'bn' ? 'কোন সক্রিয় ট্রিপ পাওয়া যায়নি' : 'No active trips found' }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Selected Vehicle Verification & Gate Pass Card (2 cols) -->
        <div class="lg:col-span-2">
            @if($selectedTrip)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                    <!-- Top Status Strip -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
                        <div>
                            <span class="text-xs text-slate-400 uppercase font-semibold tracking-wider">
                                {{ app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট নম্বর' : 'Trip Request No' }}:
                            </span>
                            <span class="font-mono font-bold text-slate-800 text-sm ml-1">{{ $selectedTrip->request_no }}</span>
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $badgeColor }}">
                                {{ $selectedTrip->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Vehicle & Driver Identity Card -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                        <!-- Vehicle Identity -->
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">
                                🚚 {{ app()->getLocale() === 'bn' ? 'গাড়ির তথ্য' : 'Vehicle Details' }}
                            </span>
                            <div class="text-lg font-bold text-slate-900">
                                {{ $selectedTrip->vehicle->registration_no ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-slate-600">
                                {{ $selectedTrip->vehicle->brand ?? '' }} {{ $selectedTrip->vehicle->model_name ?? '' }} 
                                &bull; <span class="font-semibold">{{ $selectedTrip->vehicle->vehicle_type ?? '' }}</span>
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ app()->getLocale() === 'bn' ? 'গাড়ির বর্তমান ওডোমিটার:' : 'Current Odometer:' }} 
                                <span class="font-mono font-bold text-orange-700">{{ $selectedTrip->vehicle->current_odometer ?? 0 }} KM</span>
                            </div>
                        </div>

                        <!-- Driver Identity -->
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">
                                👨‍✈️ {{ app()->getLocale() === 'bn' ? 'চালকের তথ্য' : 'Driver Identity' }}
                            </span>
                            <div class="flex items-center gap-3">
                                @if($selectedTrip->driver?->photo)
                                    <img src="{{ asset('storage/' . $selectedTrip->driver->photo) }}" class="w-12 h-12 rounded-full object-cover border border-slate-300" alt="Driver" />
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold text-lg">
                                        {{ substr($selectedTrip->driver->name ?? 'D', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-bold text-slate-900">{{ $selectedTrip->driver->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-600">
                                        {{ app()->getLocale() === 'bn' ? 'অফিস আইডি:' : 'Office ID:' }} 
                                        <span class="font-mono font-bold text-orange-800">{{ $selectedTrip->driver->office_id_card ?? 'N/A' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500">📞 {{ $selectedTrip->driver->phone ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Route Summary -->
                    <div class="bg-slate-100/70 border border-slate-200 p-4 rounded-xl flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-500 block text-[11px]">{{ app()->getLocale() === 'bn' ? 'প্রস্থানস্থল' : 'Origin' }}</span>
                            <span class="font-bold text-slate-800">{{ $selectedTrip->origin_name }}</span>
                        </div>
                        <div class="text-center px-4">
                            <span class="text-orange-600 font-bold">➔</span>
                            @if($selectedTrip->expected_distance_km)
                                <span class="block text-[10px] text-slate-500 font-mono">{{ $selectedTrip->expected_distance_km }} KM (Est)</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="text-slate-500 block text-[11px]">{{ app()->getLocale() === 'bn' ? 'গন্তব্যস্থল' : 'Destination' }}</span>
                            <span class="font-bold text-slate-800">{{ $selectedTrip->destination_name }}</span>
                        </div>
                    </div>

                    <!-- Actions: Gate Out or Gate In depending on Status -->
                    <div class="border-t border-slate-100 pt-6">
                        @if(in_array($selectedTrip->status, ['SUBMITTED', 'HOD_APPROVED', 'DISPATCHED']))
                            <!-- GATE OUT Section -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 text-orange-800 font-bold text-sm">
                                    <span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span>
                                    <span>{{ app()->getLocale() === 'bn' ? 'গাড়ি প্রস্থান রেকর্ড (Record Gate-Out)' : 'Record Gate-Out (Departure)' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                             {{ app()->getLocale() === 'bn' ? 'প্রস্থানকালীন ওডোমিটার (KM)' : 'Departure Odometer (KM)' }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" wire:model="start_odometer" 
                                               class="w-full text-base font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-3 border bg-white" />
                                        @error('start_odometer') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" wire:click="recordGateOut"
                                                class="w-full py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-sm shadow-md shadow-orange-200 transition flex items-center justify-center gap-2">
                                            <span>🚪➔</span>
                                            <span>{{ app()->getLocale() === 'bn' ? 'গেট আউট অনুমোদন করুন (GATE OUT)' : 'Approve & Record GATE OUT' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        @elseif(in_array($selectedTrip->status, ['GATE_OUT', 'IN_TRIP']))
                            <!-- GATE IN Section -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 text-slate-900 font-bold text-sm">
                                    <span class="w-3 h-3 rounded-full bg-orange-600 inline-block"></span>
                                    <span>{{ app()->getLocale() === 'bn' ? 'গাড়ি প্রত্যাবর্তন রেকর্ড (Record Gate-In)' : 'Record Gate-In (Return & Mileage Audit)' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                             {{ app()->getLocale() === 'bn' ? 'প্রত্যাবর্তনকালীন ওডোমিটার (KM)' : 'Return Odometer (KM)' }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" wire:model="end_odometer" 
                                               placeholder="e.g. {{ ($selectedTrip->start_odometer ?? 0) + 70 }}"
                                               class="w-full text-base font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-3 border bg-white" />
                                        <span class="text-[11px] text-slate-500 mt-1 block">
                                            {{ app()->getLocale() === 'bn' ? 'প্রস্থান ওডোমিটার ছিল:' : 'Start Odometer was:' }} {{ $selectedTrip->start_odometer }} KM
                                        </span>
                                        @error('end_odometer') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" wire:click="recordGateIn"
                                                class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-sm shadow-md shadow-slate-300 transition flex items-center justify-center gap-2">
                                            <span>➔🚪</span>
                                            <span>{{ app()->getLocale() === 'bn' ? 'গেট ইন ও দূরত্ব অডিট সম্পন্ন (GATE IN)' : 'Complete GATE IN & Distance Audit' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        @elseif($selectedTrip->status === 'COMPLETED')
                            <!-- Completed summary -->
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-xs">
                                <div class="font-bold text-emerald-900 text-sm mb-1">
                                    ✓ {{ app()->getLocale() === 'bn' ? 'ট্রিপ সম্পন্ন হয়েছে (Trip Completed)' : 'Trip Completed' }}
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-slate-600 mt-2">
                                    <div>
                                        <span class="text-slate-400 block">{{ app()->getLocale() === 'bn' ? 'শুরুর ওডোমিটার' : 'Start Odo' }}</span>
                                        <span class="font-mono font-bold text-slate-800">{{ $selectedTrip->start_odometer }} KM</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">{{ app()->getLocale() === 'bn' ? 'শেষের ওডোমিটার' : 'End Odo' }}</span>
                                        <span class="font-mono font-bold text-slate-800">{{ $selectedTrip->end_odometer }} KM</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">{{ app()->getLocale() === 'bn' ? 'মোট দূরত্ব' : 'Actual KM' }}</span>
                                        <span class="font-mono font-bold text-emerald-700">{{ $selectedTrip->claimed_distance_km }} KM</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">{{ app()->getLocale() === 'bn' ? 'অডিট অবস্থা' : 'Audit Status' }}</span>
                                        @if($selectedTrip->is_distance_anomaly)
                                            <span class="font-bold text-red-600">⚠️ Anomaly (+{{ $selectedTrip->distance_variance_percentage }}%)</span>
                                        @else
                                            <span class="font-bold text-emerald-600">✓ Verified OK</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 shadow-sm">
                    <span class="text-4xl block mb-2">🛡️</span>
                    <p class="text-sm font-medium">
                        {{ app()->getLocale() === 'bn' ? 'বাম পাশের তালিকা থেকে একটি ট্রিপ নির্বাচন করুন অথবা সার্চ করুন।' : 'Select an active trip from the left list or search by vehicle registration.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
