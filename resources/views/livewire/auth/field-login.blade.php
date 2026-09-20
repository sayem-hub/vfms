<div class="max-w-md mx-auto py-4 px-2 space-y-6">
    <!-- Brand & Security Header -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white shadow-lg shadow-orange-500/20 text-3xl font-black mb-1">
            🚚
        </div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">
            {{ app()->getLocale() === 'bn' ? 'ভিএফএমএস ফিল্ড লগইন' : 'VFMS Field Portal Login' }}
        </h2>
        <p class="text-sm font-medium text-slate-500 max-w-xs mx-auto leading-snug">
            {{ app()->getLocale() === 'bn' 
                ? 'ড্রাইভার, সিকিউরিটি গার্ড ও ফিল্ড কর্মীদের দ্রুত লগইন পোর্টাল' 
                : 'Rapid authentication for drivers, security guards, and fleet officers' }}
        </p>
    </div>

    <!-- Error / Feedback Alert -->
    @if($errorMessage)
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center gap-3 shadow-xs animate-shake">
            <span class="text-xl">⚠️</span>
            <div class="flex-1">{{ $errorMessage }}</div>
        </div>
    @endif

    <!-- Main Login Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50 p-6 sm:p-8 space-y-5">
        <form wire:submit="authenticate" class="space-y-5">
            <!-- Identifier (Mobile No / Employee ID) -->
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                    {{ app()->getLocale() === 'bn' ? 'মোবাইল নম্বর অথবা এমপ্লয়ি আইডি' : 'Mobile Number or Employee ID' }}
                    <span class="text-orange-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 text-lg pointer-events-none">
                        📱
                    </span>
                    <input type="text" wire:model="identifier" autofocus
                           placeholder="{{ app()->getLocale() === 'bn' ? 'যেমন: 01711000001 বা EMP-DRV-1042' : 'e.g. 01711000001 or EMP-DRV-1042' }}"
                           class="w-full pl-12 pr-4 py-3.5 text-base font-semibold rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition bg-slate-50 focus:bg-white text-slate-900 placeholder:text-slate-400" />
                </div>
                @error('identifier')
                    <span class="text-xs font-bold text-red-600 block pl-1">{{ $message }}</span>
                @enderror
                <p class="text-xs text-slate-500 pl-1">
                    {{ app()->getLocale() === 'bn' ? 'আপনার ১১ ডিজিটের মোবাইল নম্বর অথবা প্রাতিষ্ঠানিক আইডি কার্ড নম্বর লিখুন।' : 'Enter your 11-digit registered mobile or employee punch ID.' }}
                </p>
            </div>

            <!-- PIN Input -->
            <div class="space-y-2" x-data="{ showPin: false }">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        {{ app()->getLocale() === 'bn' ? '৪ ডিজিটের সিক্রেট পিন (PIN)' : '4-Digit Secret PIN' }}
                        <span class="text-orange-600">*</span>
                    </label>
                    <button type="button" @click="showPin = !showPin" class="text-xs font-bold text-orange-600 hover:text-orange-700">
                        <span x-text="showPin ? '{{ app()->getLocale() === 'bn' ? 'লুকান' : 'Hide' }}' : '{{ app()->getLocale() === 'bn' ? 'দেখুন' : 'Show' }}'"></span>
                    </button>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 text-lg pointer-events-none">
                        🔑
                    </span>
                    <input :type="showPin ? 'text' : 'password'" wire:model="pin" inputmode="numeric" maxlength="12"
                           placeholder="••••"
                           class="w-full pl-12 pr-4 py-3.5 text-xl tracking-widest font-mono font-black rounded-2xl border-slate-300 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition bg-slate-50 focus:bg-white text-slate-900 placeholder:text-slate-300" />
                </div>
                @error('pin')
                    <span class="text-xs font-bold text-red-600 block pl-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="remember"
                           class="w-5 h-5 rounded-lg border-slate-300 text-orange-600 focus:ring-orange-500" />
                    <span class="text-sm font-semibold text-slate-700">
                        {{ app()->getLocale() === 'bn' ? 'এই ফোনে মনে রাখুন' : 'Remember on this phone' }}
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-4 px-6 bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-black text-base rounded-2xl shadow-lg shadow-orange-600/25 transition active:scale-[0.98] flex items-center justify-center gap-2">
                <span>{{ app()->getLocale() === 'bn' ? 'লগইন করুন' : 'Authenticate & Sign In' }}</span>
                <span class="text-lg">➔</span>
            </button>
        </form>

        <!-- Quick One-Click Testing Shortcuts for Dev/Demo -->
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">
                {{ app()->getLocale() === 'bn' ? '⚡ টেস্ট অ্যাকাউন্ট নির্বাচন করুন (PIN: 1234)' : '⚡ Quick Test Logins (PIN: 1234)' }}
            </div>
            <div class="grid grid-cols-1 gap-2">
                <!-- Driver Quick Fill -->
                <button type="button" wire:click="fillQuickCredentials('01711000001', '1234')"
                        class="p-3 text-left rounded-xl border border-slate-200 hover:border-orange-400 hover:bg-orange-50/50 transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🛞</span> Md. Rafiqul Islam <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-blue-100 text-blue-800">DRIVER</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">Mobile: 01711000001 / EMP-DRV-1042</div>
                    </div>
                    <span class="text-xs font-bold text-orange-600">Select ➔</span>
                </button>

                <!-- Security Guard Quick Fill -->
                <button type="button" wire:click="fillQuickCredentials('01711999001', '1234')"
                        class="p-3 text-left rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50 transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🛡️</span> BK Bari Gate Security <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-emerald-100 text-emerald-800">GUARD</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">Mobile: 01711999001 / GUARD-GZP-01</div>
                    </div>
                    <span class="text-xs font-bold text-emerald-600">Select ➔</span>
                </button>

                <!-- Transport Officer Quick Fill -->
                <button type="button" wire:click="fillQuickCredentials('01711000000', '1234')"
                        class="p-3 text-left rounded-xl border border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                            <span>⚙️</span> Transport General Manager <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-slate-200 text-slate-800">ADMIN</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">Mobile: 01711000000 / EMP-ADM-001</div>
                    </div>
                    <span class="text-xs font-bold text-slate-700">Select ➔</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Security Guarantee Badge -->
    <div class="text-center text-xs text-slate-400 flex items-center justify-center gap-2">
        <span>🔒</span>
        <span>{{ app()->getLocale() === 'bn' ? 'এনজেড গ্রুপ সিকিউর ফ্লিট অথেনটিকেশন প্রটোকল' : 'NZ Group Enterprise Fleet Security Protocol' }}</span>
    </div>
</div>
