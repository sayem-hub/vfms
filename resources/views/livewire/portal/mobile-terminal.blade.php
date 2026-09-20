<div class="max-w-md mx-auto space-y-4 pb-12" x-data="{
    isOnline: navigator.onLine,
    init() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            $wire.syncPendingOutbox();
        });
        window.addEventListener('offline', () => {
            this.isOnline = false;
        });

        // Fetch device GPS
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    $wire.updateGpsCoordinates(pos.coords.latitude, pos.coords.longitude);
                },
                (err) => console.log('GPS error:', err),
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
    }
}">
    <!-- Mobile Status Strip -->
    <div class="rounded-2xl p-4 shadow-sm border text-white transition"
         :class="isOnline ? 'bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900 border-orange-500/40' : 'bg-gradient-to-r from-amber-950 via-amber-900 to-zinc-900 border-amber-500/60'">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                          :class="isOnline ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3"
                          :class="isOnline ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                </span>
                <span class="text-xs font-bold uppercase tracking-wide"
                      x-text="isOnline ? '{{ app()->getLocale() === 'bn' ? 'অনলাইন মোড' : 'ONLINE MODE' }}' : '{{ app()->getLocale() === 'bn' ? 'অফলাইন মোড (লোকাল ক্যাশ)' : 'OFFLINE MODE (CACHED)' }}'">
                </span>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full bg-white/10 text-slate-300">
                    ID: {{ $deviceId }}
                </span>
            </div>
        </div>

        <!-- GPS Geofence Pill -->
        <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-xs">
            <div class="flex items-center gap-1.5 text-slate-300">
                <span>📍</span>
                <span class="truncate max-w-[220px] font-medium">{{ $nearestGeofenceName ?? 'Factory Network' }}</span>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $isWithinGeofence ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-500/20 text-slate-300' }}">
                {{ $isWithinGeofence ? 'In Geofence' : 'Transit' }}
            </span>
        </div>
    </div>

    <!-- Feedback Banner -->
    @if($feedbackMessage)
        <div class="p-3 rounded-xl text-xs font-semibold {{ $feedbackType === 'success' ? 'bg-emerald-50 text-emerald-900 border border-emerald-300' : ($feedbackType === 'warning' ? 'bg-amber-50 text-amber-900 border border-amber-300' : 'bg-blue-50 text-blue-900 border border-blue-300') }}">
            {{ $feedbackMessage }}
        </div>
    @endif

    <!-- Vehicle Selector -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                {{ app()->getLocale() === 'bn' ? 'যানবাহন নির্বাচন' : 'Select Vehicle' }}
            </label>
            <select wire:model.live="selectedVehicleId"
                    class="w-full text-sm font-semibold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white">
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}">
                        {{ $v->registration_no }} ({{ $v->dedicated_to_official ?? $v->vehicle_type }})
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedVehicle)
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'বর্তমান অবস্থা' : 'Current Status' }}</span>
                    <span class="font-bold {{ $selectedVehicle->status === 'ON_TRIP' ? 'text-amber-700' : 'text-emerald-700' }}">
                        {{ $selectedVehicle->status === 'ON_TRIP' ? '🟡 বাইরে চলমান (On Trip)' : '🟢 কারখানায় প্রস্তুত (Available)' }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">{{ app()->getLocale() === 'bn' ? 'ওডোমিটার' : 'Odometer' }}</span>
                    <span class="font-mono font-black text-slate-900">{{ number_format($selectedVehicle->current_odometer) }} KM</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Touch Punch Grid -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Gate Out Button -->
        <button type="button" wire:click="$set('activeAction', 'GATE_OUT')"
                class="p-4 rounded-2xl border text-left transition shadow-sm flex flex-col justify-between {{ $activeAction === 'GATE_OUT' ? 'bg-orange-50 border-orange-500 ring-2 ring-orange-500/20' : 'bg-white border-slate-200 hover:border-orange-400' }}">
            <div class="text-2xl mb-2">🚀</div>
            <div>
                <div class="font-bold text-sm text-slate-900">{{ app()->getLocale() === 'bn' ? 'গেট আউট' : 'Gate Out' }}</div>
                <div class="text-[11px] text-slate-500">{{ app()->getLocale() === 'bn' ? 'গাড়ি প্রস্থান পাঞ্চ' : 'Vehicle departure' }}</div>
            </div>
        </button>

        <!-- Gate In Button -->
        <button type="button" wire:click="$set('activeAction', 'GATE_IN')"
                class="p-4 rounded-2xl border text-left transition shadow-sm flex flex-col justify-between {{ $activeAction === 'GATE_IN' ? 'bg-emerald-50 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white border-slate-200 hover:border-emerald-400' }}">
            <div class="text-2xl mb-2">🏁</div>
            <div>
                <div class="font-bold text-sm text-slate-900">{{ app()->getLocale() === 'bn' ? 'গেট ইন' : 'Gate In' }}</div>
                <div class="text-[11px] text-slate-500">{{ app()->getLocale() === 'bn' ? 'গাড়ি ফেরত পাঞ্চ' : 'Vehicle return' }}</div>
            </div>
        </button>

        <!-- Hardware Camera Button -->
        <button type="button" wire:click="$set('activeAction', 'CAMERA')"
                class="p-4 rounded-2xl border text-left transition shadow-sm flex flex-col justify-between {{ $activeAction === 'CAMERA' ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-500/20' : 'bg-white border-slate-200 hover:border-blue-400' }}">
            <div class="text-2xl mb-2">📸</div>
            <div>
                <div class="font-bold text-sm text-slate-900">{{ app()->getLocale() === 'bn' ? 'লাইভ ক্যামেরা' : 'Live Camera' }}</div>
                <div class="text-[11px] text-slate-500">{{ app()->getLocale() === 'bn' ? 'রসিদ ও মিটার প্রুফ' : 'Tamper-proof capture' }}</div>
            </div>
        </button>

        <!-- Outbox Sync Button -->
        <button type="button" wire:click="syncPendingOutbox"
                class="p-4 rounded-2xl border text-left transition shadow-sm flex flex-col justify-between bg-white border-slate-200 hover:border-purple-400">
            <div class="flex items-center justify-between text-2xl mb-2">
                <span>🔄</span>
                @if($outboxCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500 text-white">
                        {{ $outboxCount }}
                    </span>
                @endif
            </div>
            <div>
                <div class="font-bold text-sm text-slate-900">{{ app()->getLocale() === 'bn' ? 'আউটবক্স সিঙ্ক' : 'Sync Outbox' }}</div>
                <div class="text-[11px] text-slate-500">
                    {{ $outboxCount > 0 ? "{$outboxCount} " . (app()->getLocale() === 'bn' ? 'টি অপেক্ষমাণ' : 'pending') : (app()->getLocale() === 'bn' ? 'সব আপ-টু-ডেট' : 'All up to date') }}
                </div>
            </div>
        </button>
    </div>

    <!-- Action Forms -->
    @if($activeAction === 'GATE_OUT')
        <div class="bg-white rounded-2xl border border-orange-200 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h4 class="font-bold text-sm text-slate-900">🚀 {{ app()->getLocale() === 'bn' ? 'গেট আউট এন্ট্রি' : 'Record Gate Out' }}</h4>
                <button type="button" wire:click="$set('activeAction', 'OVERVIEW')" class="text-slate-400 hover:text-slate-600 text-xs">✕</button>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'প্রস্থান ওডোমিটার (KM)' : 'Departure Odometer (KM)' }} <span class="text-red-500">*</span>
                </label>
                <input type="number" wire:model="odometerReading" placeholder="{{ $selectedVehicle?->current_odometer }}"
                       class="w-full text-base font-mono font-bold rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
                @error('odometerReading') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ app()->getLocale() === 'bn' ? 'গন্তব্যস্থল' : 'Destination' }}</label>
                <input type="text" wire:model="destination" placeholder="e.g. Corporate Head Office / Bhobanipur"
                       class="w-full text-sm rounded-xl border-slate-300 focus:border-orange-500 focus:ring-orange-500 p-2.5 border bg-white" />
            </div>

            <button type="button" wire:click="submitGateOut"
                    class="w-full py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                {{ app()->getLocale() === 'bn' ? 'গেট আউট নিশ্চিত করুন' : 'Confirm Gate Out' }}
            </button>
        </div>
    @endif

    @if($activeAction === 'GATE_IN')
        <div class="bg-white rounded-2xl border border-emerald-200 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h4 class="font-bold text-sm text-slate-900">🏁 {{ app()->getLocale() === 'bn' ? 'গেট ইন এন্ট্রি' : 'Record Gate In' }}</h4>
                <button type="button" wire:click="$set('activeAction', 'OVERVIEW')" class="text-slate-400 hover:text-slate-600 text-xs">✕</button>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'ফেরত ওডোমিটার (KM)' : 'Return Odometer (KM)' }} <span class="text-red-500">*</span>
                </label>
                <input type="number" wire:model="odometerReading" placeholder="{{ $selectedVehicle?->current_odometer }}"
                       class="w-full text-base font-mono font-bold rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 p-2.5 border bg-white" />
                @error('odometerReading') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="button" wire:click="submitGateIn"
                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                {{ app()->getLocale() === 'bn' ? 'গেট ইন নিশ্চিত করুন' : 'Confirm Gate In' }}
            </button>
        </div>
    @endif

    @if($activeAction === 'CAMERA')
        <div class="bg-white rounded-2xl border border-blue-200 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h4 class="font-bold text-sm text-slate-900">📸 {{ app()->getLocale() === 'bn' ? 'লাইভ ক্যামেরা প্রুফ আপলোড' : 'Tamper-Proof Camera Capture' }}</h4>
                <button type="button" wire:click="$set('activeAction', 'OVERVIEW')" class="text-slate-400 hover:text-slate-600 text-xs">✕</button>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                {{ app()->getLocale() === 'bn' 
                    ? 'গ্যালারি নয়, শুধুমাত্র সরাসরি মোবাইল ক্যামেরা দিয়ে ছবি তুলুন। ছবিতে স্বয়ংক্রিয়ভাবে টাইমস্ট্যাম্প ও জিপিএস কোঅর্ডিনেট যুক্ত হবে।' 
                    : 'Direct native camera capture only. Watermarked with GPS coordinates, registration, and timestamp.' }}
            </p>

            <!-- Direct Hardware Camera Input with capture="environment" -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    {{ app()->getLocale() === 'bn' ? 'ক্যামেরা দিয়ে ছবি তুলুন' : 'Capture Photo via Camera' }}
                </label>
                <input type="file" wire:model="capturedPhoto" accept="image/*" capture="environment"
                       class="w-full text-xs file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 p-2 border border-slate-300 rounded-xl bg-slate-50" />
                @error('capturedPhoto') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            @if($capturedPhoto)
                <button type="button" wire:click="processCameraCapture"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                    {{ app()->getLocale() === 'bn' ? 'ওয়াটারমার্ক বার্ন ও সেভ করুন' : 'Apply Watermark & Save' }}
                </button>
            @endif

            @if($watermarkedPhotoUrl)
                <div class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase block">✓ {{ app()->getLocale() === 'bn' ? 'ওয়াটারমার্কযুক্ত প্রুফ' : 'Watermarked Proof' }}</span>
                    <img src="{{ $watermarkedPhotoUrl }}" alt="Watermarked Proof" class="rounded-xl border border-slate-200 shadow-xs max-h-64 mx-auto object-contain" />
                </div>
            @endif
        </div>
    @endif

    <!-- Offline Outbox Summary -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-2">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 text-xs">
            <span class="font-bold text-slate-800 uppercase text-[10px] tracking-wider">
                {{ app()->getLocale() === 'bn' ? 'অফলাইন আউটবক্স লগ' : 'Offline Outbox Queue' }}
            </span>
            <span class="text-slate-400 font-mono">{{ $outboxCount }} pending</span>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            @forelse($recentOutbox as $o)
                <div class="py-2 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">{{ $o->action_type }}</span>
                        <span class="block text-[10px] text-slate-400 font-mono">{{ $o->client_recorded_at->format('d M H:i:s') }}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $o->status === 'SYNCED' ? 'bg-emerald-100 text-emerald-800' : ($o->status === 'FAILED' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                        {{ $o->status }}
                    </span>
                </div>
            @empty
                <div class="py-4 text-center text-slate-400 text-xs">
                    {{ app()->getLocale() === 'bn' ? 'কোনো অপেক্ষমাণ আউটবক্স রেকর্ড নেই।' : 'No outbox queue items.' }}
                </div>
            @endforelse
        </div>
    </div>
</div>
