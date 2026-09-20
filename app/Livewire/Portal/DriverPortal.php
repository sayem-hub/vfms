<?php

namespace App\Livewire\Portal;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequest;
use App\Models\Vehicle;
use App\Services\Audit\FuelEfficiencyService;
use App\Services\Settlement\TripExpenseSettlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class DriverPortal extends Component
{
    use WithFileUploads;

    public ?int $selectedDriverId = null;

    public ?int $activeTripId = null;

    // Fuel Logging fields
    #[Rule('nullable|string|min:3')]
    public string $station_name = '';

    #[Rule('nullable|numeric|min:0')]
    public ?int $fuel_odometer = null;

    #[Rule('nullable|numeric|gt:0')]
    public ?float $fuel_quantity = null;

    #[Rule('nullable|numeric|gt:0')]
    public ?float $fuel_unit_price = null;

    public string $fuel_type = 'OCTANE';

    public string $fuel_payment_method = 'PETTY_CASH_ADVANCE';

    #[Rule('nullable|image|max:10240')]
    public $dispenser_photo;

    #[Rule('nullable|image|max:10240')]
    public $odometer_photo;

    #[Rule('nullable|image|max:10240')]
    public $receipt_memo_photo;

    // Expense Settlement fields
    public float $advance_cash = 0.0;

    public float $toll_expense = 0.0;

    public float $parking_expense = 0.0;

    public float $food_allowance = 0.0;

    public float $emergency_repair = 0.0;

    public float $other_expense = 0.0;

    public ?string $feedbackMessage = null;

    public ?string $feedbackType = 'success';

    public function mount(): void
    {
        $user = Auth::user();

        // Enforce Driver Role Scoping
        if ($user?->isDriver()) {
            $driver = $user->driver ?? Driver::where('user_id', $user->id)->first();
            if (! $driver && $user->employee_id) {
                $driver = Driver::where('office_id_card', $user->employee_id)->first();
            }

            if (! $driver) {
                abort(403, app()->getLocale() === 'bn'
                    ? 'আপনার ইউজার অ্যাকাউন্টের সাথে কোনো ড্রাইভার প্রোফাইল যুক্ত নেই। অ্যাডমিনের সাথে যোগাযোগ করুন।'
                    : 'No driver profile linked to your user account.');
            }

            $this->selectDriver($driver->id);

            return;
        }

        // For fleet administrators or transport officers, default to first active driver
        $firstDriver = Driver::where('is_active', true)->first();
        if ($firstDriver) {
            $this->selectDriver($firstDriver->id);
        }
    }

    public function selectDriver(int $driverId): void
    {
        $user = Auth::user();

        // Strict Driver Isolation Check: A driver can never select or inspect another driver's ID
        if ($user?->isDriver()) {
            $linkedDriver = $user->driver ?? Driver::where('user_id', $user->id)->first();
            if (! $linkedDriver && $user->employee_id) {
                $linkedDriver = Driver::where('office_id_card', $user->employee_id)->first();
            }

            if ($linkedDriver && $driverId !== $linkedDriver->id) {
                abort(403, app()->getLocale() === 'bn'
                    ? 'অননুমোদিত অ্যাক্সেস। আপনি শুধুমাত্র আপনার নিজস্ব তথ্য দেখতে পারবেন।'
                    : 'Access denied. You can only view your own assigned records.');
            }
        }

        $this->selectedDriverId = $driverId;
        $driver = Driver::with('currentVehicle')->find($driverId);

        if ($driver) {
            $this->fuel_odometer = $driver->currentVehicle?->current_odometer ?? 0;
            $this->fuel_type = $driver->currentVehicle?->fuel_type ?? 'OCTANE';

            // Find current active trip or latest trip for this driver
            $trip = TripRequest::where('driver_id', $driverId)
                ->whereIn('status', ['GATE_OUT', 'IN_TRIP', 'DISPATCHED', 'HOD_APPROVED', 'COMPLETED'])
                ->latest()
                ->first();

            $this->activeTripId = $trip?->id;

            // Load existing settlement if present
            if ($trip) {
                $settlement = TripExpenseSettlement::where('trip_request_id', $trip->id)->first();
                if ($settlement) {
                    $this->advance_cash = (float) $settlement->advance_cash_received;
                    $this->toll_expense = (float) $settlement->total_toll_expense;
                    $this->parking_expense = (float) $settlement->total_parking_expense;
                    $this->food_allowance = (float) $settlement->total_driver_food_allowance;
                    $this->emergency_repair = (float) $settlement->total_emergency_repair_expense;
                    $this->other_expense = (float) $settlement->total_other_expense;
                }
            }
        }
    }

    public function submitFuelLog(FuelEfficiencyService $efficiencyService): void
    {
        $this->validate([
            'station_name' => 'required|string|min:3',
            'fuel_odometer' => 'required|numeric|min:0',
            'fuel_quantity' => 'required|numeric|gt:0',
            'fuel_unit_price' => 'required|numeric|gt:0',
            'dispenser_photo' => 'nullable|image|max:10240',
            'odometer_photo' => 'nullable|image|max:10240',
            'receipt_memo_photo' => 'nullable|image|max:10240',
        ]);

        $driver = Driver::with('currentVehicle')->findOrFail($this->selectedDriverId);
        $vehicle = $driver->currentVehicle ?: Vehicle::first();

        $dispenserPath = $this->dispenser_photo?->store('fuel/dispensers', 'public');
        $odoPath = $this->odometer_photo?->store('fuel/odometers', 'public');
        $receiptPath = $this->receipt_memo_photo?->store('fuel/receipts', 'public');

        $totalCost = round($this->fuel_quantity * $this->fuel_unit_price, 2);

        $fuelLog = new FuelLog([
            'trip_request_id' => $this->activeTripId,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'fuel_type' => $this->fuel_type,
            'refill_date' => now(),
            'station_name' => $this->station_name,
            'odometer_reading' => $this->fuel_odometer,
            'fuel_quantity' => $this->fuel_quantity,
            'unit_price' => $this->fuel_unit_price,
            'total_cost' => $totalCost,
            'payment_method' => $this->fuel_payment_method,
            'dispenser_photo' => $dispenserPath,
            'odometer_photo' => $odoPath,
            'receipt_memo_photo' => $receiptPath,
        ]);

        $fuelLog->save();

        // Update vehicle odometer if higher
        if ($this->fuel_odometer > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $this->fuel_odometer]);
        }

        // Run automated audit and discrepancy check
        $auditLog = $efficiencyService->auditFuelLog($fuelLog);

        $this->feedbackType = $auditLog->is_efficiency_anomaly ? 'warning' : 'success';
        $this->feedbackMessage = $auditLog->is_efficiency_anomaly
            ? (app()->getLocale() === 'bn'
                ? "ফুয়েল এন্ট্রি সংরক্ষিত হয়েছে। সতর্কতা: অডিট রাডারে অসঙ্গতি শনাক্ত হয়েছে ({$auditLog->calculated_km_per_liter} KM/L)।"
                : "Fuel log recorded with audit alert ({$auditLog->calculated_km_per_liter} KM/L).")
            : (app()->getLocale() === 'bn'
                ? 'ফুয়েল এন্ট্রি ও অডিট ভেরিফিকেশন সফল হয়েছে।'
                : 'Fuel log submitted and audit verified successfully.');

        // Reset inputs
        $this->station_name = '';
        $this->fuel_quantity = null;
        $this->fuel_unit_price = null;
        $this->dispenser_photo = null;
        $this->odometer_photo = null;
        $this->receipt_memo_photo = null;
    }

    public function submitSettlement(TripExpenseSettlementService $settlementService): void
    {
        if (! $this->activeTripId) {
            $this->feedbackType = 'warning';
            $this->feedbackMessage = app()->getLocale() === 'bn' ? 'কোনো সক্রিয় ট্রিপ পাওয়া যায়নি।' : 'No active trip to settle.';

            return;
        }

        $settlement = TripExpenseSettlement::firstOrNew(['trip_request_id' => $this->activeTripId]);
        $settlement->driver_id = $this->selectedDriverId;
        $settlement->advance_cash_received = $this->advance_cash;
        $settlement->total_toll_expense = $this->toll_expense;
        $settlement->total_parking_expense = $this->parking_expense;
        $settlement->total_driver_food_allowance = $this->food_allowance;
        $settlement->total_emergency_repair_expense = $this->emergency_repair;
        $settlement->total_other_expense = $this->other_expense;
        $settlement->status = 'DRAFT_BY_DRIVER';

        $settlementService->syncAndCalculate($settlement);

        $this->feedbackType = 'success';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? 'ট্রিপ খরচ ও ভাউচার হিসাব সফলভাবে জমা হয়েছে। চূড়ান্ত সমন্বয়ের জন্য ক্যাশিয়ারের নিকট উপস্থাপিত হবে।'
            : 'Trip expenses submitted successfully for accounts settlement.';
    }

    public function render(): View
    {
        $user = Auth::user();
        $isDriverUser = $user?->isDriver();

        if ($isDriverUser) {
            $driver = $user->driver ?? Driver::where('user_id', $user->id)->first();
            if (! $driver && $user->employee_id) {
                $driver = Driver::where('office_id_card', $user->employee_id)->first();
            }
            $drivers = $driver ? collect([$driver]) : collect();
        } else {
            $drivers = Driver::where('is_active', true)->with('currentVehicle')->get();
            $driver = Driver::with('currentVehicle')->find($this->selectedDriverId);
        }

        $trip = null;
        $fuelLogs = collect();
        $totalFuelCost = 0.0;

        if ($this->activeTripId) {
            $trip = TripRequest::with(['vehicle', 'driver'])->find($this->activeTripId);
            $fuelLogs = FuelLog::where('trip_request_id', $this->activeTripId)->latest()->get();
            $totalFuelCost = (float) $fuelLogs->sum('total_cost');
        } elseif ($driver?->currentVehicle) {
            $fuelLogs = FuelLog::where('vehicle_id', $driver->currentVehicle->id)->latest('id')->take(5)->get();
            $totalFuelCost = (float) $fuelLogs->sum('total_cost');
        }

        $totalExpenses = $totalFuelCost + $this->toll_expense + $this->parking_expense + $this->food_allowance + $this->emergency_repair + $this->other_expense;
        $netBalance = $this->advance_cash - $totalExpenses;

        $monthlyFuelQuota = null;
        $monthlyFuelConsumed = 0.0;
        $monthlyFuelRemaining = null;
        $monthlyFuelPercent = null;

        if ($driver?->currentVehicle && $driver->currentVehicle->monthly_fuel_quota_liters) {
            $v = $driver->currentVehicle;
            $monthlyFuelQuota = (float) $v->monthly_fuel_quota_liters;
            $monthlyFuelConsumed = $v->monthlyFuelConsumedLiters();
            $monthlyFuelRemaining = $v->monthlyFuelQuotaRemaining();
            $monthlyFuelPercent = $v->monthlyFuelQuotaUsagePercent();
        }

        return view('livewire.portal.driver-portal', [
            'drivers' => $drivers,
            'driver' => $driver,
            'isDriverUser' => $isDriverUser,
            'trip' => $trip,
            'fuelLogs' => $fuelLogs,
            'totalFuelCost' => $totalFuelCost,
            'totalExpenses' => $totalExpenses,
            'netBalance' => $netBalance,
            'monthlyFuelQuota' => $monthlyFuelQuota,
            'monthlyFuelConsumed' => $monthlyFuelConsumed,
            'monthlyFuelRemaining' => $monthlyFuelRemaining,
            'monthlyFuelPercent' => $monthlyFuelPercent,
        ]);
    }
}
