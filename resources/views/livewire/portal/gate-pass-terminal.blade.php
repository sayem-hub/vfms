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
                    {{ app()->getLocale() === 'bn' ? 'অন-ডিমান্ড রিকোয়েস্ট ট্রিপ ও নির্ধারিত গাড়ি/বাসের সরাসরি ইন-আউট পাঞ্চ' : 'Live Gate-Out / Gate-In recording for on-demand trips, management cars, and fixed staff buses' }}
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

        <!-- Terminal Tabs -->
        <div class="flex items-center gap-2 mt-6 pt-4 border-t border-slate-700/60">
            <button type="button" wire:click="switchTab('trips')"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 {{ $activeTab === 'trips' ? 'bg-orange-600 text-white shadow-md shadow-orange-950/40' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                <span>📋</span>
                <span>{{ app()->getLocale() === 'bn' ? 'রিকোয়েস্ট ভিত্তিক ট্রিপ' : 'On-Demand Trip Requests' }}</span>
            </button>
            <button type="button" wire:click="switchTab('fixed')"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 {{ $activeTab === 'fixed' ? 'bg-orange-600 text-white shadow-md shadow-orange-950/40' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                <span>🚌</span>
                <span>{{ app()->getLocale() === 'bn' ? 'নির্ধারিত গাড়ি ও স্টাফ বাস (Fixed & Commute)' : 'Fixed Commute & Management Cars' }}</span>
            </button>
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

    {{-- ========================================================================= --}}
    {{-- TAB 1: ON-DEMAND TRIP REQUESTS                                           --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'trips')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Search & Active Trips Queue (1 col) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center justify-between">
                    <span>🔍 {{ app()->getLocale() === 'bn' ? 'রিকোয়েস্ট অনুসন্ধান' : 'Find Trip Request' }}</span>
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
                                    $tripBadge = match($trip->status) {
                                        'GATE_OUT', 'IN_TRIP' => 'bg-purple-100 text-purple-800',
                                        'HOD_APPROVED', 'DISPATCHED' => 'bg-blue-100 text-blue-800',
                                        'COMPLETED' => 'bg-emerald-100 text-emerald-800',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                @endphp
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $tripBadge }}">
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
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-100 text-orange-800">
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
                                            <span class="text-slate-400 block">{{ app()->getLocale() === 'bn' ? 'মোট চালিত দূরত্ব' : 'Total Traveled' }}</span>
                                            <span class="font-mono font-bold text-emerald-700">{{ $selectedTrip->claimed_distance_km }} KM</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block">{{ app()->getLocale() === 'bn' ? 'দূরত্ব অডিট' : 'Audit Status' }}</span>
                                            <span class="font-bold {{ $selectedTrip->is_distance_anomaly ? 'text-red-600' : 'text-emerald-700' }}">
                                                {{ $selectedTrip->is_distance_anomaly ? 'অস্বাভাবিক (Anomaly)' : 'যাচাইকৃত (OK)' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                        {{ app()->getLocale() === 'bn' ? 'বাম পাশের তালিকা থেকে একটি ট্রিপ নির্বাচন করুন' : 'Select a trip from the left queue to record Gate Pass' }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: FIXED COMMUTE & DEDICATED MANAGEMENT CARS                          --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'fixed')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Fixed Vehicles Roster (1 col) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center justify-between">
                    <span>🚌 {{ app()->getLocale() === 'bn' ? 'স্থায়ী ও ম্যানেজমেন্ট গাড়ি' : 'Fixed & Assigned Vehicles' }}</span>
                    <span class="text-xs px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full font-mono">{{ $fixedVehicles->count() }}</span>
                </h2>

                <!-- Search input -->
                <div>
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                           placeholder="{{ app()->getLocale() === 'bn' ? 'গাড়ির নম্বর / কর্মকর্তা / রুট...' : 'Vehicle No / Official / Route...' }}"
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-3 border bg-slate-50 font-medium" />
                </div>

                <!-- Vehicles List -->
                <div class="space-y-2 max-h-[560px] overflow-y-auto pr-1">
                    @forelse($fixedVehicles as $v)
                        @php
                            $isOut = $v->activeGateLog() !== null;
                            $firstDriver = $v->drivers->first();
                            $firstRoute = $v->fixedRoutes->first();
                        @endphp
                        <button type="button" wire:click="selectFixedVehicle({{ $v->id }})"
                                class="w-full text-left p-3.5 rounded-xl border transition {{ $selectedFixedVehicleId === $v->id ? 'bg-orange-50 border-orange-400 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 border-slate-200' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-900 font-mono">{{ $v->registration_no }}</span>
                                @if($isOut)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                                        <span>{{ app()->getLocale() === 'bn' ? 'বাইরে চলমান (OUT)' : 'OUT' }}</span>
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        <span>{{ app()->getLocale() === 'bn' ? 'উপস্থিত (IN)' : 'IN' }}</span>
                                    </span>
                                @endif
                            </div>

                            <div class="text-[11px] font-semibold text-slate-700 mt-1">
                                @if($v->dedicated_to_official)
                                    👔 {{ $v->dedicated_to_official }}
                                @elseif($firstRoute)
                                    🚌 {{ $firstRoute->route_name }}
                                @else
                                    🏷️ {{ $v->vehicle_type }}
                                @endif
                            </div>

                            <div class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                                <span>👨‍✈️ {{ $firstDriver->name ?? 'ড্রাইভার নির্ধারিত নেই' }}</span>
                                <span class="font-mono font-bold text-slate-600">{{ $v->current_odometer }} KM</span>
                            </div>
                        </button>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">
                            {{ app()->getLocale() === 'bn' ? 'কোন নির্ধারিত গাড়ি পাওয়া যায়নি' : 'No fixed or dedicated vehicles found' }}
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Direct Gate Punch Terminal & Today's Log (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                @if($selectedFixedVehicle)
                    @php
                        $activeLog = $selectedFixedVehicle->activeGateLog();
                        $assignedDriver = $selectedFixedVehicle->drivers->first();
                        $assignedRoute = $selectedFixedVehicle->fixedRoutes->first();
                    @endphp

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                        <!-- Top Vehicle Header Strip -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
                            <div>
                                <span class="text-xs text-slate-400 uppercase font-semibold tracking-wider">
                                    {{ app()->getLocale() === 'bn' ? 'স্থায়ী / নির্ধারিত গাড়ি' : 'Fixed / Assigned Vehicle' }}
                                </span>
                                <h3 class="text-xl font-black text-slate-900 font-mono mt-0.5">
                                    {{ $selectedFixedVehicle->registration_no }}
                                </h3>
                                <span class="text-xs text-slate-500">
                                    {{ $selectedFixedVehicle->brand }} {{ $selectedFixedVehicle->model_name }} &bull; {{ $selectedFixedVehicle->vehicle_type }}
                                </span>
                            </div>
                            <div>
                                @if($activeLog)
                                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>{{ app()->getLocale() === 'bn' ? 'বর্তমানে কারখানার বাইরে (OUT)' : 'Currently OUT of Factory' }}</span>
                                    </span>
                                @else
                                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>{{ app()->getLocale() === 'bn' ? 'বর্তমানে কারখানায় উপস্থিত (IN)' : 'Currently IN Factory' }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Overview Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                            <div>
                                <span class="text-slate-500 block text-[11px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'দায়িত্বপ্রাপ্ত / রুট' : 'Official / Route' }}</span>
                                <span class="font-bold text-slate-800 text-sm">
                                    {{ $selectedFixedVehicle->dedicated_to_official ?: ($assignedRoute->route_name ?? 'Regular Factory Duty') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[11px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'নির্ধারিত চালক' : 'Assigned Driver' }}</span>
                                <span class="font-bold text-slate-800 text-sm">
                                    👨‍✈️ {{ $assignedDriver->name ?? 'Unassigned' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[11px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'বর্তমান ওডোমিটার' : 'Current Odometer' }}</span>
                                <span class="font-bold text-orange-600 font-mono text-sm">
                                    {{ $selectedFixedVehicle->current_odometer }} KM
                                </span>
                            </div>
                        </div>

                        <!-- Interactive Gate Punch Forms -->
                        <div class="border-t border-slate-100 pt-6">
                            @if(! $activeLog)
                                <!-- PUNCH GATE OUT -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 text-orange-800 font-bold text-sm">
                                        <span class="w-3 h-3 rounded-full bg-orange-600 inline-block"></span>
                                        <span>🚪➔ {{ app()->getLocale() === 'bn' ? 'গেট আউট পাঞ্চ করুন (RECORD GATE OUT)' : 'RECORD GATE OUT' }}</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Driver Select -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                                {{ app()->getLocale() === 'bn' ? 'চালক নির্বাচন' : 'Driver' }}
                                            </label>
                                            <select wire:model="fixedDriverId" class="w-full text-xs font-medium rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white">
                                                <option value="">-- ড্রাইভার নির্বাচন করুন --</option>
                                                @foreach($allDrivers as $d)
                                                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->office_id_card ?? 'ID-N/A' }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Route or Destination -->
                                        @if($selectedFixedVehicle->isStaffBus())
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                                    {{ app()->getLocale() === 'bn' ? 'স্টাফ বাস রুট' : 'Fixed Route' }}
                                                </label>
                                                <select wire:model="fixedRouteId" class="w-full text-xs font-medium rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white">
                                                    <option value="">-- রুট নির্বাচন করুন --</option>
                                                    @foreach($allFixedRoutes as $r)
                                                        <option value="{{ $r->id }}">{{ $r->route_name }} ({{ $r->standard_distance_km ?? 0 }} KM)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                                    {{ app()->getLocale() === 'bn' ? 'যাত্রী / কর্মকর্তা' : 'Official / Passenger' }}
                                                </label>
                                                <input type="text" wire:model="fixedOfficialName" 
                                                       placeholder="e.g. MD Sir / Director SCM"
                                                       class="w-full text-xs font-medium rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                                            </div>
                                        @endif

                                        <!-- Destination -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                                {{ app()->getLocale() === 'bn' ? 'গন্তব্যস্থল' : 'Destination' }}
                                            </label>
                                            <input type="text" wire:model="fixedDestination" 
                                                   placeholder="e.g. Baridhara HO / Mawna Plant / Joydebpur"
                                                   class="w-full text-xs font-medium rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                                        </div>

                                        <!-- Purpose -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                                {{ app()->getLocale() === 'bn' ? 'যাত্রার উদ্দেশ্য' : 'Purpose' }}
                                            </label>
                                            <input type="text" wire:model="fixedPurpose" 
                                                   placeholder="e.g. Morning Staff Drop / Official Visit"
                                                   class="w-full text-xs font-medium rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                                        </div>

                                        <!-- Departure Odometer -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                                {{ app()->getLocale() === 'bn' ? 'প্রস্থান ওডোমিটার (KM)' : 'Departure Odometer (KM)' }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" wire:model="fixedOutOdometer" 
                                                   class="w-full text-base font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                                            @error('fixedOutOdometer') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Action Button -->
                                        <div class="flex items-end">
                                            <button type="button" wire:click="recordFixedGateOut"
                                                    class="w-full py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-sm shadow-md shadow-orange-200 transition flex items-center justify-center gap-2">
                                                <span>🚪➔</span>
                                                <span>{{ app()->getLocale() === 'bn' ? 'গেট আউট অনুমোদন করুন (PUNCH OUT)' : 'PUNCH GATE OUT' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            @else
                                <!-- PUNCH GATE IN -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 text-slate-900 font-bold text-sm">
                                        <span class="w-3 h-3 rounded-full bg-emerald-600 inline-block"></span>
                                        <span>➔🚪 {{ app()->getLocale() === 'bn' ? 'গেট ইন পাঞ্চ করুন (RECORD GATE IN)' : 'RECORD GATE IN' }}</span>
                                    </div>

                                    <!-- Departure Context Box -->
                                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs grid grid-cols-2 sm:grid-cols-4 gap-3 text-slate-700">
                                        <div>
                                            <span class="text-slate-500 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'প্রস্থানের সময়' : 'Departure Time' }}</span>
                                            <span class="font-bold text-slate-900">{{ $activeLog->gate_out_time?->format('h:i A') ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'প্রস্থান ওডোমিটার' : 'Departure Odo' }}</span>
                                            <span class="font-mono font-bold text-slate-900">{{ $activeLog->out_odometer }} KM</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'কর্মকর্তা / রুট' : 'Official / Route' }}</span>
                                            <span class="font-bold text-slate-900 truncate block">{{ $activeLog->official_name ?: ($activeLog->fixedRoute?->route_name ?? 'N/A') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'চালক' : 'Driver' }}</span>
                                            <span class="font-bold text-slate-900">{{ $activeLog->driver?->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                                {{ app()->getLocale() === 'bn' ? 'প্রবেশকালীন ওডোমিটার (KM)' : 'Arrival Odometer (KM)' }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" wire:model.live="fixedInOdometer" 
                                                   placeholder="e.g. {{ ($activeLog->out_odometer ?? 0) + 30 }}"
                                                   class="w-full text-base font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-3 border bg-white" />
                                            @if($fixedInOdometer && $fixedInOdometer >= ($activeLog->out_odometer ?? 0))
                                                <span class="text-[11px] font-bold text-emerald-700 mt-1 block">
                                                    {{ app()->getLocale() === 'bn' ? 'মোট ভ্রমণ হবে:' : 'Total run:' }} {{ $fixedInOdometer - $activeLog->out_odometer }} KM
                                                </span>
                                            @endif
                                            @error('fixedInOdometer') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="flex items-end">
                                            <button type="button" wire:click="recordFixedGateIn"
                                                    class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-sm shadow-md shadow-slate-300 transition flex items-center justify-center gap-2">
                                                <span>➔🚪</span>
                                                <span>{{ app()->getLocale() === 'bn' ? 'ফ্যাক্টরিতে প্রবেশ সম্পন্ন করুন (PUNCH IN)' : 'CONFIRM GATE IN' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Today's Fixed Movement Log Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                            <span>📖</span>
                            <span>{{ app()->getLocale() === 'bn' ? 'আজকের স্থায়ী ও ম্যানেজমেন্ট গাড়ি চলাচলের লগ' : "Today's Fixed Movement Register" }}</span>
                        </h3>
                        <span class="text-xs font-mono text-slate-500">{{ now()->format('d M Y') }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold uppercase text-[10px]">
                                    <th class="p-2.5">{{ app()->getLocale() === 'bn' ? 'গাড়ি নং' : 'Vehicle' }}</th>
                                    <th class="p-2.5">{{ app()->getLocale() === 'bn' ? 'কর্মকর্তা / রুট' : 'Official / Route' }}</th>
                                    <th class="p-2.5">{{ app()->getLocale() === 'bn' ? 'চালক' : 'Driver' }}</th>
                                    <th class="p-2.5 text-center">{{ app()->getLocale() === 'bn' ? 'প্রস্থান' : 'Out' }}</th>
                                    <th class="p-2.5 text-center">{{ app()->getLocale() === 'bn' ? 'প্রবেশ' : 'In' }}</th>
                                    <th class="p-2.5 text-right">{{ app()->getLocale() === 'bn' ? 'দূরত্ব (কিমি)' : 'Total KM' }}</th>
                                    <th class="p-2.5 text-center">{{ app()->getLocale() === 'bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($todayGateLogs as $log)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="p-2.5 font-bold font-mono text-slate-900">
                                            {{ $log->vehicle?->registration_no }}
                                        </td>
                                        <td class="p-2.5 text-slate-700">
                                            {{ $log->official_name ?: ($log->fixedRoute?->route_name ?? 'General Duty') }}
                                        </td>
                                        <td class="p-2.5 text-slate-600">
                                            {{ $log->driver?->name ?? 'N/A' }}
                                        </td>
                                        <td class="p-2.5 text-center">
                                            <div class="font-semibold text-slate-800">{{ $log->gate_out_time?->format('h:i A') ?? '-' }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $log->out_odometer }} KM</div>
                                        </td>
                                        <td class="p-2.5 text-center">
                                            <div class="font-semibold text-slate-800">{{ $log->gate_in_time?->format('h:i A') ?? '-' }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $log->in_odometer ? $log->in_odometer . ' KM' : '-' }}</div>
                                        </td>
                                        <td class="p-2.5 text-right font-mono font-bold text-slate-800">
                                            {{ $log->total_km !== null ? $log->total_km . ' KM' : '-' }}
                                        </td>
                                        <td class="p-2.5 text-center">
                                            @if($log->status === 'OUT')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">OUT</span>
                                            @elseif($log->status === 'COMPLETED')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">IN</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ $log->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-6 text-center text-slate-400 text-xs">
                                            {{ app()->getLocale() === 'bn' ? 'আজকের দিনে কোন স্থায়ী চলাচল রেকর্ড করা হয়নি।' : 'No fixed movement logged today yet.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
