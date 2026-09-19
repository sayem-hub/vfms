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
        // Default to first active driver
        $firstDriver = Driver::where('is_active', true)->first();
        if ($firstDriver) {
            $this->selectDriver($firstDriver->id);
        }
    }

    public function selectDriver(int $driverId): void
    {
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

        // Audit burn rate
        $efficiencyService->auditFuelLog($fuelLog);

        // Update settlement fuel expense
        if ($this->activeTripId) {
            $settlement = TripExpenseSettlement::firstOrNew(['trip_request_id' => $this->activeTripId]);
            $settlement->driver_id = $driver->id;
            $settlement->total_fuel_expense = FuelLog::where('trip_request_id', $this->activeTripId)->sum('total_cost');
            $settlement->recalculate();
            $settlement->save();
        }

        if ($fuelLog->is_efficiency_anomaly) {
            $this->feedbackType = 'warning';
            $this->feedbackMessage = app()->getLocale() === 'bn'
                ? "জ্বালানী রিফিল সফলভাবে সংরক্ষিত হয়েছে। তবে মাইলেজ পূর্ববর্তী গড়ের চেয়ে ২০% কম ({$fuelLog->calculated_km_per_liter} কিমি/লিটার)।"
                : "Fuel saved successfully. Flagged for efficiency drop ({$fuelLog->calculated_km_per_liter} KM/L).";
        } else {
            $this->feedbackType = 'success';
            $this->feedbackMessage = app()->getLocale() === 'bn'
                ? "জ্বালানী রিফিল সফলভাবে সংরক্ষিত হয়েছে। মাইলেজ স্বাভাবিক: {$fuelLog->calculated_km_per_liter} কিমি/লিটার।"
                : "Fuel log recorded successfully. Calculated: {$fuelLog->calculated_km_per_liter} KM/L.";
        }

        $this->reset(['station_name', 'fuel_quantity', 'fuel_unit_price', 'dispenser_photo', 'odometer_photo', 'receipt_memo_photo']);
    }

    public function submitSettlement(TripExpenseSettlementService $settlementService): void
    {
        if (! $this->activeTripId) {
            $this->feedbackType = 'warning';
            $this->feedbackMessage = 'No active trip to settle.';

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
        $drivers = Driver::where('is_active', true)->with('currentVehicle')->get();
        $driver = Driver::with('currentVehicle')->find($this->selectedDriverId);
        $trip = null;
        $fuelLogs = collect();
        $totalFuelCost = 0.0;

        if ($this->activeTripId) {
            $trip = TripRequest::with(['vehicle', 'driver'])->find($this->activeTripId);
            $fuelLogs = FuelLog::where('trip_request_id', $this->activeTripId)->latest()->get();
            $totalFuelCost = (float) $fuelLogs->sum('total_cost');
        }

        $totalExpenses = $totalFuelCost + $this->toll_expense + $this->parking_expense + $this->food_allowance + $this->emergency_repair + $this->other_expense;
        $netBalance = $this->advance_cash - $totalExpenses;

        return view('livewire.portal.driver-portal', [
            'drivers' => $drivers,
            'driver' => $driver,
            'trip' => $trip,
            'fuelLogs' => $fuelLogs,
            'totalFuelCost' => $totalFuelCost,
            'totalExpenses' => $totalExpenses,
            'netBalance' => $netBalance,
        ]);
    }
}
