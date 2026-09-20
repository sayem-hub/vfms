<div class="space-y-8 max-w-4xl mx-auto pb-12">
    <!-- Driver Mobile Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border-l-8 border-orange-600">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="mb-2">
                    <span class="inline-block px-3 py-1 bg-orange-600/30 rounded-full text-xs font-black text-orange-200 border border-orange-500/40 uppercase tracking-wider">
                        🛞 {{ app()->getLocale() === 'bn' ? 'ড্রাইভার ফিল্ড অপারেশন' : 'Driver Field Operations' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                    {{ app()->getLocale() === 'bn' ? 'জ্বালানী ও ট্রিপ খরচ এন্ট্রি' : 'Fuel & Expense Settlement' }}
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    {{ app()->getLocale() === 'bn' ? 'তেল রিফিল, টোল ও ভাউচার খরচ তাৎক্ষণিক হিসাব করুন' : 'Log fuel refills with meter photos and reconcile trip petty cash' }}
                </p>
            </div>

            <!-- Identity: Locked Profile for Driver OR Switcher for Supervisor -->
            @if($isDriverUser && $driver)
                <!-- Authenticated Driver Locked Profile Badge -->
                <div class="bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20 flex items-center gap-3.5 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                        🛞
                    </div>
                    <div>
                        <div class="text-base font-black text-white leading-tight">{{ $driver->name }}</div>
                        <div class="text-xs font-mono font-bold text-orange-300 mt-0.5">
                            ID: {{ $driver->office_id_card ?? 'ID-N/A' }} &bull; {{ $driver->phone }}
                        </div>
                    </div>
                </div>
            @else
                <!-- Admin/Supervisor Driver Switcher Dropdown -->
                <div class="bg-white/10 p-3 rounded-2xl border border-white/20 min-w-[260px]">
                    <label class="block text-xs text-orange-200 uppercase font-black mb-1.5">
                        {{ app()->getLocale() === 'bn' ? 'তত্ত্বাবধায়ক: চালক নির্বাচন' : 'Supervisor: Select Driver' }}
                    </label>
                    <select wire:change="selectDriver($event.target.value)" class="text-sm bg-white text-slate-900 rounded-xl p-3 font-bold w-full border-0 focus:ring-2 focus:ring-orange-500">
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ $selectedDriverId === $d->id ? 'selected' : '' }}>
                                {{ $d->name }} ({{ $d->office_id_card ?? 'ID-N/A' }}) - {{ $d->currentVehicle->registration_no ?? 'No Vehicle' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <!-- Feedback Notification -->
    @if($feedbackMessage)
        <div class="rounded-2xl p-5 border shadow-sm transition {{ $feedbackType === 'warning' ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900' }}">
            <div class="flex items-center gap-3 text-base font-bold">
                <span class="text-2xl">{{ $feedbackType === 'warning' ? '⚠️' : '✅' }}</span>
                <span>{{ $feedbackMessage }}</span>
            </div>
        </div>
    @endif

    <!-- Active Trip or Assigned Vehicle Banner -->
    @if($trip)
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-slate-500 font-black uppercase tracking-wider block">
                    {{ app()->getLocale() === 'bn' ? 'বর্তমান সক্রিয় ট্রিপ' : 'Current Active Trip' }}
                </span>
                <span class="font-mono font-black text-slate-900 text-lg">{{ $trip->request_no }}</span>
                <span class="text-sm font-semibold text-slate-600 block mt-1">
                    📍 {{ $trip->origin_name }} ➔ {{ $trip->destination_name }}
                </span>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-xs text-slate-500 font-bold block">{{ app()->getLocale() === 'bn' ? 'নির্ধারিত গাড়ি' : 'Assigned Vehicle' }}</span>
                    <span class="text-base font-black text-orange-700 font-mono">{{ $trip->vehicle->registration_no ?? 'N/A' }}</span>
                </div>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-indigo-100 text-indigo-800">
                    {{ $trip->status }}
                </span>
            </div>
        </div>
    @elseif($driver?->currentVehicle)
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-slate-500 font-black uppercase tracking-wider block">
                    {{ app()->getLocale() === 'bn' ? 'নির্ধারিত স্থায়ী গাড়ি / ডিউটি' : 'Assigned Vehicle / Duty' }}
                </span>
                <div class="font-black text-slate-900 text-lg flex items-center gap-2 mt-0.5">
                    <span class="font-mono text-orange-700">{{ $driver->currentVehicle->registration_no }}</span>
                    <span class="text-sm text-slate-500 font-normal">({{ $driver->currentVehicle->brand }} {{ $driver->currentVehicle->model_name }})</span>
                </div>
                <span class="text-sm font-semibold text-slate-600 block mt-1">
                    {{ $driver->currentVehicle->dedicated_to_official ? '👔 ' . $driver->currentVehicle->dedicated_to_official : ($driver->currentVehicle->isStaffBus() ? '🚌 স্টাফ পরিবহন বাস' : '🏷️ ' . $driver->currentVehicle->vehicle_type) }}
                </span>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-xs text-slate-500 font-bold block">{{ app()->getLocale() === 'bn' ? 'বর্তমান ওডোমিটার' : 'Current Odometer' }}</span>
                    <span class="text-base font-black text-slate-900 font-mono">{{ number_format($driver->currentVehicle->current_odometer) }} KM</span>
                </div>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                    {{ app()->getLocale() === 'bn' ? 'স্থায়ী দায়িত্ব' : 'Dedicated Duty' }}
                </span>
            </div>
        </div>
    @endif

    <!-- Monthly Fuel Quota Tracker Widget -->
    @if($monthlyFuelQuota)
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 text-white rounded-3xl p-6 sm:p-8 shadow-md space-y-5 border-l-8 border-orange-500">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-orange-400 block">
                        ⛽ {{ app()->getLocale() === 'bn' ? 'মাসিক ফুয়েল কোটা স্ট্যাটাস' : 'Monthly Fuel Quota Tracker' }}
                    </span>
                    <h3 class="text-lg font-black text-white mt-1">
                        {{ now()->format('F Y') }} - {{ $driver->currentVehicle->registration_no }}
                    </h3>
                </div>
                <div>
                    @if($monthlyFuelPercent > 100)
                        <span class="px-4 py-1.5 rounded-full text-xs font-black bg-red-600/40 text-red-200 border border-red-500/50">
                            ⚠️ {{ app()->getLocale() === 'bn' ? 'কোটা অতিক্রান্ত (>১০০%)' : 'Quota Exceeded (>100%)' }}
                        </span>
                    @elseif($monthlyFuelPercent >= 80)
                        <span class="px-4 py-1.5 rounded-full text-xs font-black bg-amber-600/40 text-amber-200 border border-amber-500/50">
                            ⚠️ {{ app()->getLocale() === 'bn' ? 'কোটার ৮০% শেষ' : '80% Quota Used' }}
                        </span>
                    @else
                        <span class="px-4 py-1.5 rounded-full text-xs font-black bg-emerald-600/40 text-emerald-200 border border-emerald-500/50">
                            ✓ {{ app()->getLocale() === 'bn' ? 'কোটা স্বাভাবিক' : 'Quota Normal' }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-2 text-center">
                <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'মাসিক বরাদ্দ' : 'Monthly Quota' }}</span>
                    <span class="font-mono font-black text-xl text-white">{{ number_format($monthlyFuelQuota, 1) }}</span>
                    <span class="text-xs text-slate-400 block">Liters</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'চলতি মাসে ব্যবহৃত' : 'Consumed' }}</span>
                    <span class="font-mono font-black text-xl text-orange-400">{{ number_format($monthlyFuelConsumed, 1) }}</span>
                    <span class="text-xs text-slate-400 block">Liters ({{ $monthlyFuelPercent }}%)</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                    <span class="text-slate-400 block text-xs uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'অবশিষ্ট কোটা' : 'Remaining' }}</span>
                    <span class="font-mono font-black text-xl {{ $monthlyFuelRemaining < 0 ? 'text-red-400' : 'text-emerald-400' }}">
                        {{ number_format($monthlyFuelRemaining, 1) }}
                    </span>
                    <span class="text-xs text-slate-400 block">Liters</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div>
                <div class="w-full bg-white/10 rounded-full h-3.5 overflow-hidden p-0.5">
                    <div class="h-full rounded-full transition-all duration-500 {{ $monthlyFuelPercent > 100 ? 'bg-red-500' : ($monthlyFuelPercent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                         style="width: {{ min(100, $monthlyFuelPercent) }}%"></div>
                </div>
            </div>
        </div>
    @endif

    <!-- Section 1: Fuel Refill Entry Form -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2.5 pb-4 border-b border-slate-100">
            <span class="text-2xl">⛽</span>
            <span>{{ app()->getLocale() === 'bn' ? 'জ্বালানী / গ্যাস রিফিল তথ্য এন্ট্রি' : 'Fuel / Gas Refill Entry' }}</span>
        </h2>

        <form wire:submit="submitFuelLog" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                        {{ app()->getLocale() === 'bn' ? 'পাম্প / ফিলিং স্টেশনের নাম' : 'Filling Station Name' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="station_name" placeholder="e.g. Trust Filling Station, Joydebpur" 
                           class="w-full text-base font-semibold rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
                    @error('station_name') <span class="text-xs font-bold text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                        {{ app()->getLocale() === 'bn' ? 'বর্তমান ওডোমিটার / কিমি' : 'Current Odometer (KM)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="fuel_odometer" 
                           class="w-full text-base font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
                    @error('fuel_odometer') <span class="text-xs font-bold text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                        {{ app()->getLocale() === 'bn' ? 'পরিমাণ (লিটার / ঘনমিটার)' : 'Quantity (Liters / m³)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" wire:model="fuel_quantity" placeholder="e.g. 35.0"
                           class="w-full text-base font-semibold rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
                    @error('fuel_quantity') <span class="text-xs font-bold text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                        {{ app()->getLocale() === 'bn' ? 'প্রতি ইউনিট মূল্য (টাকা)' : 'Unit Price (BDT)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" wire:model="fuel_unit_price" placeholder="e.g. 125.00"
                           class="w-full text-base font-semibold rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
                    @error('fuel_unit_price') <span class="text-xs font-bold text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                        {{ app()->getLocale() === 'bn' ? 'পেমেন্ট পদ্ধতি' : 'Payment Method' }}
                    </label>
                    <select wire:model="fuel_payment_method" class="w-full text-base font-semibold rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white">
                        <option value="PETTY_CASH_ADVANCE">অগ্রিম ক্যাশ থেকে (Petty Cash)</option>
                        <option value="DRIVER_POCKET_REIMBURSABLE">ড্রাইভারের নিজস্ব টাকা (পরিশোধযোগ্য)</option>
                        <option value="COMPANY_CREDIT_VOUCHER">কোম্পানি ক্রেডিট ভাউচার</option>
                    </select>
                </div>
            </div>

            <!-- Mandatory 3-Point Photo Proof Upload Grid -->
            <div class="bg-slate-50 border border-slate-200 rounded-3xl p-5 space-y-4">
                <span class="text-sm font-black text-slate-800 uppercase tracking-wide block flex items-center gap-2">
                    <span class="text-lg">📷</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'বাধ্যতামূলক ৩-পয়েন্ট ছবি প্রমাণ (3-Point Proof Verification):' : 'Mandatory 3-Point Live Photo Proof:' }}</span>
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-2">
                        <label class="block text-xs font-black text-slate-700">
                            1. {{ app()->getLocale() === 'bn' ? 'পাম্প ডিসপেনসার মিটার' : 'Dispenser Meter Screen' }}
                        </label>
                        <input type="file" wire:model="dispenser_photo" accept="image/*" capture="environment"
                               class="text-xs w-full text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-100 file:text-orange-800" />
                        @error('dispenser_photo') <span class="text-xs text-red-600 block mt-1 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-2">
                        <label class="block text-xs font-black text-slate-700">
                            2. {{ app()->getLocale() === 'bn' ? 'গাড়ির ড্যাশবোর্ড ওডোমিটার' : 'Dashboard Odometer' }}
                        </label>
                        <input type="file" wire:model="odometer_photo" accept="image/*" capture="environment"
                               class="text-xs w-full text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-100 file:text-orange-800" />
                        @error('odometer_photo') <span class="text-xs text-red-600 block mt-1 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-2">
                        <label class="block text-xs font-black text-slate-700">
                            3. {{ app()->getLocale() === 'bn' ? 'ক্যাশ মেমো / রশিদ' : 'Cash Memo / Receipt' }}
                        </label>
                        <input type="file" wire:model="receipt_memo_photo" accept="image/*" capture="environment"
                               class="text-xs w-full text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-orange-100 file:text-orange-800" />
                        @error('receipt_memo_photo') <span class="text-xs text-red-600 block mt-1 font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-black text-base shadow-lg shadow-orange-600/25 transition active:scale-[0.98]">
                    + {{ app()->getLocale() === 'bn' ? 'জ্বালানী রিফিল সংরক্ষণ করুন' : 'Save Fuel Refill' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Trip Expenses & Live Petty Cash Balance Calculator -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2.5 pb-4 border-b border-slate-100">
            <span class="text-2xl">💰</span>
            <span>{{ app()->getLocale() === 'bn' ? 'টোল, পার্কিং ও ট্রিপ খরচ সমন্বয়' : 'Tolls, Parking & Petty Cash Settlement' }}</span>
        </h2>

        <!-- Expense Inputs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                    {{ app()->getLocale() === 'bn' ? 'গৃহীত অগ্রিম ক্যাশ টাকা (BDT)' : 'Advance Cash Received (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="advance_cash" 
                       class="w-full text-base font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
            </div>

            <div>
                <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                    {{ app()->getLocale() === 'bn' ? 'মোট টোল খরচ (টাকা)' : 'Total Tolls (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="toll_expense" 
                       class="w-full text-base font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
            </div>

            <div>
                <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                    {{ app()->getLocale() === 'bn' ? 'পার্কিং ফি (টাকা)' : 'Parking Fees (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="parking_expense" 
                       class="w-full text-base font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
            </div>

            <div>
                <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                    {{ app()->getLocale() === 'bn' ? 'ড্রাইভার খোরাকী / DA (টাকা)' : 'Food Allowance / DA (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="food_allowance" 
                       class="w-full text-base font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
            </div>

            <div>
                <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                    {{ app()->getLocale() === 'bn' ? 'জরুরি মেরামত / পাংচার (টাকা)' : 'Emergency Repair (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="emergency_repair" 
                       class="w-full text-base font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
            </div>

            <div>
                <label class="block text-sm font-black text-slate-700 uppercase mb-2">
                    {{ app()->getLocale() === 'bn' ? 'অন্যান্য খরচ (টাকা)' : 'Other Misc (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="other_expense" 
                       class="w-full text-base font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 p-3.5 border bg-white" />
            </div>
        </div>

        <!-- Live Net Balance Summary Card -->
        <div class="bg-slate-900 text-white p-6 sm:p-8 rounded-3xl shadow-lg space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-800">
                <div>
                    <span class="text-xs text-slate-400 block font-bold uppercase tracking-wider">
                        {{ app()->getLocale() === 'bn' ? 'গৃহীত অগ্রিম নগদ টাকা' : 'Total Advance Cash Received' }}
                    </span>
                    <span class="text-2xl sm:text-3xl font-mono font-black text-white">BDT {{ number_format($advance_cash, 2) }}</span>
                </div>
                <div class="sm:text-right">
                    <span class="text-xs text-slate-400 block font-bold uppercase tracking-wider">
                        {{ app()->getLocale() === 'bn' ? 'মোট প্রকৃত খরচ (তেল + টোল + খোরাকী + অন্যান্য)' : 'Total Actual Expenses (Fuel + Toll + DA)' }}
                    </span>
                    <span class="text-2xl sm:text-3xl font-mono font-black text-amber-400">BDT {{ number_format($totalExpenses, 2) }}</span>
                </div>
            </div>

            <!-- Net Balance Result -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                <div>
                    <span class="text-xs text-slate-400 block font-bold uppercase tracking-wider">
                        {{ app()->getLocale() === 'bn' ? 'চূড়ান্ত হিসাব সমন্বয় / ব্যালেন্স:' : 'Net Settlement Balance:' }}
                    </span>
                    @if($netBalance > 0)
                        <div class="text-2xl font-black text-emerald-400 mt-1">
                            + BDT {{ number_format($netBalance, 2) }}
                            <span class="text-xs font-semibold text-slate-300 block">({{ app()->getLocale() === 'bn' ? 'কোম্পানিকে ফেরতযোগ্য অতিরিক্ত টাকা' : 'Refundable to company cashier' }})</span>
                        </div>
                    @elseif($netBalance < 0)
                        <div class="text-2xl font-black text-rose-400 mt-1">
                            - BDT {{ number_format(abs($netBalance), 2) }}
                            <span class="text-xs font-semibold text-slate-300 block">({{ app()->getLocale() === 'bn' ? 'ড্রাইভারকে প্রদেয় বকেয়া টাকা' : 'Payable by company to driver' }})</span>
                        </div>
                    @else
                        <div class="text-2xl font-black text-slate-300 mt-1">
                            BDT 0.00 (Balanced)
                        </div>
                    @endif
                </div>

                <div>
                    <button type="button" wire:click="submitSettlement"
                            class="w-full sm:w-auto px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-black text-base shadow-xl shadow-orange-900/50 transition active:scale-[0.98]">
                        ✓ {{ app()->getLocale() === 'bn' ? 'হিসাব সমন্বয় জমা দিন' : 'Submit Settlement to Accounts' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
