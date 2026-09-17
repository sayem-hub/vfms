<div class="space-y-8 max-w-4xl mx-auto">
    <!-- Driver Mobile Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 rounded-2xl p-6 text-white shadow-md border-l-4 border-orange-600">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="mb-2">
                    <span class="inline-block px-2.5 py-0.5 bg-orange-600/30 rounded-full text-[11px] font-bold text-orange-200 border border-orange-500/40 uppercase tracking-wide">
                        🛞 {{ app()->getLocale() === 'bn' ? 'ড্রাইভার ও ফিল্ড পোর্টাল' : 'Driver Field Operations' }}
                    </span>
                </div>
                <h1 class="text-2xl font-black">
                    {{ app()->getLocale() === 'bn' ? 'জ্বালানী ও ট্রিপ খরচ এন্ট্রি' : 'Fuel & Expense Settlement' }}
                </h1>
                <p class="text-slate-300 text-xs mt-1">
                    {{ app()->getLocale() === 'bn' ? 'যাত্রাপথে তেল রিফিল, টোল ও ভাউচার খরচ তাৎক্ষণিক হিসাব করুন' : 'Log fuel refills with meter photos and reconcile trip petty cash' }}
                </p>
            </div>

            <!-- Driver Switcher Dropdown -->
            <div class="bg-white/10 p-2 rounded-xl border border-white/20">
                <label class="block text-[10px] text-orange-200 uppercase font-bold mb-1">
                    {{ app()->getLocale() === 'bn' ? 'চালকের আইডি নির্বাচন:' : 'Select Driver Identity:' }}
                </label>
                <select wire:change="selectDriver($event.target.value)" class="text-xs bg-white text-slate-900 rounded-lg p-2 font-bold w-full">
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" {{ $selectedDriverId === $d->id ? 'selected' : '' }}>
                            {{ $d->name }} ({{ $d->office_id_card ?? 'ID-N/A' }}) - {{ $d->currentVehicle->registration_no ?? 'No Vehicle' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Feedback Notification -->
    @if($feedbackMessage)
        <div class="rounded-2xl p-4 border shadow-sm transition {{ $feedbackType === 'warning' ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900' }}">
            <div class="flex items-center gap-2 text-sm font-bold">
                <span>{{ $feedbackType === 'warning' ? '⚠️' : '✅' }}</span>
                <span>{{ $feedbackMessage }}</span>
            </div>
        </div>
    @endif

    <!-- Active Trip Summary Banner -->
    @if($trip)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">
                    {{ app()->getLocale() === 'bn' ? 'বর্তমান সক্রিয় ট্রিপ' : 'Current Active Trip' }}
                </span>
                <span class="font-mono font-bold text-slate-900 text-sm">{{ $trip->requisition_no }}</span>
                <span class="text-xs text-slate-500 block mt-0.5">
                    📍 {{ $trip->origin_name }} ➔ {{ $trip->destination_name }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <span class="text-[11px] text-slate-400 block">{{ app()->getLocale() === 'bn' ? 'নির্ধারিত গাড়ি' : 'Assigned Vehicle' }}</span>
                    <span class="text-xs font-bold text-orange-700 font-mono">{{ $trip->vehicle->registration_no ?? 'N/A' }}</span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                    {{ $trip->status }}
                </span>
            </div>
        </div>
    @endif

    <!-- Section 1: Fuel Refill Entry Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="text-xl">⛽</span>
            <span>{{ app()->getLocale() === 'bn' ? 'জ্বালানী / গ্যাস রিফিল তথ্য এন্ট্রি (Fuel Refill)' : 'Fuel / Gas Refill Entry' }}</span>
        </h2>

        <form wire:submit="submitFuelLog" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        {{ app()->getLocale() === 'bn' ? 'পাম্প / ফিলিং স্টেশনের নাম' : 'Filling Station Name' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="station_name" placeholder="e.g. Trust Filling Station, Joydebpur" 
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                    @error('station_name') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        {{ app()->getLocale() === 'bn' ? 'বর্তমান ওডোমিটার / কিমি' : 'Current Odometer (KM)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="fuel_odometer" 
                           class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                    @error('fuel_odometer') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        {{ app()->getLocale() === 'bn' ? 'পরিমাণ (লিটার / ঘনমিটার)' : 'Quantity (Liters / m³)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" wire:model="fuel_quantity" placeholder="e.g. 35.0"
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                    @error('fuel_quantity') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        {{ app()->getLocale() === 'bn' ? 'প্রতি ইউনিট মূল্য (টাকা)' : 'Unit Price (BDT)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" wire:model="fuel_unit_price" placeholder="e.g. 125.00"
                           class="w-full text-sm rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                    @error('fuel_unit_price') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        {{ app()->getLocale() === 'bn' ? 'পেমেন্ট পদ্ধতি' : 'Payment Method' }}
                    </label>
                    <select wire:model="fuel_payment_method" class="w-full text-sm rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white">
                        <option value="PETTY_CASH_ADVANCE">অগ্রিম ক্যাশ থেকে (Petty Cash)</option>
                        <option value="DRIVER_POCKET_REIMBURSABLE">ড্রাইভারের নিজস্ব টাকা (পরিশোধযোগ্য)</option>
                        <option value="COMPANY_CREDIT_VOUCHER">কোম্পানি ক্রেডিট ভাউচার</option>
                    </select>
                </div>
            </div>

            <!-- Mandatory 3-Point Photo Proof Upload Grid -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide block flex items-center gap-1.5">
                    <span>📷</span>
                    <span>{{ app()->getLocale() === 'bn' ? 'বাধ্যতামূলক ৩-পয়েন্ট ছবি প্রমাণ (3-Point Proof Verification):' : 'Mandatory 3-Point Live Photo Proof:' }}</span>
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-white p-3 rounded-xl border border-slate-200">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">
                            1. {{ app()->getLocale() === 'bn' ? 'পাম্প ডিসপেনসার মিটার' : 'Dispenser Meter Screen' }}
                        </label>
                        <input type="file" wire:model="dispenser_photo" accept="image/*" class="text-xs w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-orange-50 file:text-orange-700" />
                        @error('dispenser_photo') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-200">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">
                            2. {{ app()->getLocale() === 'bn' ? 'গাড়ির ড্যাশবোর্ড ওডোমিটার' : 'Dashboard Odometer' }}
                        </label>
                        <input type="file" wire:model="odometer_photo" accept="image/*" class="text-xs w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-orange-50 file:text-orange-700" />
                        @error('odometer_photo') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-200">
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">
                            3. {{ app()->getLocale() === 'bn' ? 'ক্যাশ মেমো / রশিদ' : 'Cash Memo / Receipt' }}
                        </label>
                        <input type="file" wire:model="receipt_memo_photo" accept="image/*" class="text-xs w-full text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[11px] file:bg-orange-50 file:text-orange-700" />
                        @error('receipt_memo_photo') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-sm shadow-md shadow-orange-200 transition">
                    + {{ app()->getLocale() === 'bn' ? 'জ্বালানী রিফিল সংরক্ষণ করুন' : 'Save Fuel Refill' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Trip Expenses & Live Petty Cash Balance Calculator -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="text-xl">💰</span>
            <span>{{ app()->getLocale() === 'bn' ? 'টোল, পার্কিং ও ট্রিপ খরচ সমন্বয়' : 'Tolls, Parking & Petty Cash Settlement' }}</span>
        </h2>

        <!-- Expense Inputs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'গৃহীত অগ্রিম ক্যাশ টাকা (BDT)' : 'Advance Cash Received (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="advance_cash" 
                       class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'মোট টোল খরচ (টাকা)' : 'Total Tolls (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="toll_expense" 
                       class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'পার্কিং ফি (টাকা)' : 'Parking Fees (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="parking_expense" 
                       class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'ড্রাইভার খোরাকী / DA (টাকা)' : 'Food Allowance / DA (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="food_allowance" 
                       class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'জরুরি মেরামত / পাংচার (টাকা)' : 'Emergency Repair (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="emergency_repair" 
                       class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'অন্যান্য খরচ (টাকা)' : 'Other Misc (BDT)' }}
                </label>
                <input type="number" step="0.01" wire:model.live="other_expense" 
                       class="w-full text-sm font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>
        </div>

        <!-- Live Net Balance Summary Card -->
        <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                <div>
                    <span class="text-xs text-slate-400 block font-semibold uppercase tracking-wider">
                        {{ app()->getLocale() === 'bn' ? 'গৃহীত অগ্রিম নগদ টাকা' : 'Total Advance Cash Received' }}
                    </span>
                    <span class="text-2xl font-mono font-bold text-white">BDT {{ number_format($advance_cash, 2) }}</span>
                </div>
                <div class="sm:text-right">
                    <span class="text-xs text-slate-400 block font-semibold uppercase tracking-wider">
                        {{ app()->getLocale() === 'bn' ? 'মোট প্রকৃত খরচ (তেল + টোল + খোরাকী + অন্যান্য)' : 'Total Actual Expenses (Fuel + Toll + DA)' }}
                    </span>
                    <span class="text-2xl font-mono font-bold text-amber-400">BDT {{ number_format($totalExpenses, 2) }}</span>
                </div>
            </div>

            <!-- Net Balance Result -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                <div>
                    <span class="text-xs text-slate-400 block font-semibold uppercase tracking-wider">
                        {{ app()->getLocale() === 'bn' ? 'চূড়ান্ত হিসাব সমন্বয় / ব্যালেন্স:' : 'Net Settlement Balance:' }}
                    </span>
                    @if($netBalance > 0)
                        <div class="text-xl font-bold text-emerald-400">
                            + BDT {{ number_format($netBalance, 2) }}
                            <span class="text-xs font-normal text-slate-300 block">({{ app()->getLocale() === 'bn' ? 'কোম্পানিকে ফেরতযোগ্য অতিরিক্ত টাকা' : 'Refundable to company cashier' }})</span>
                        </div>
                    @elseif($netBalance < 0)
                        <div class="text-xl font-bold text-rose-400">
                            - BDT {{ number_format(abs($netBalance), 2) }}
                            <span class="text-xs font-normal text-slate-300 block">({{ app()->getLocale() === 'bn' ? 'ড্রাইভারকে প্রদেয় বকেয়া টাকা' : 'Payable by company to driver' }})</span>
                        </div>
                    @else
                        <div class="text-xl font-bold text-slate-300">
                            BDT 0.00 (Balanced)
                        </div>
                    @endif
                </div>

                <div>
                    <button type="button" wire:click="submitSettlement"
                            class="w-full sm:w-auto px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-sm shadow-md shadow-orange-900/40 transition">
                        ✓ {{ app()->getLocale() === 'bn' ? 'হিসাব সমন্বয় জমা দিন' : 'Submit Settlement to Accounts' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
