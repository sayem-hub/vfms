<?php

namespace App\Livewire\Portal;

use App\Models\Driver;
use App\Models\FixedRoute;
use App\Models\TripRequest;
use App\Models\Vehicle;
use App\Models\VehicleGateLog;
use App\Services\Audit\DistanceAuditService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GatePassTerminal extends Component
{
    public string $activeTab = 'trips'; // 'trips' or 'fixed'

    public string $searchQuery = '';

    // Request-based trip fields
    public ?int $selectedTripId = null;

    public ?int $start_odometer = null;

    public ?int $end_odometer = null;

    // Fixed & dedicated vehicles fields
    public ?int $selectedFixedVehicleId = null;

    public ?int $activeGateLogId = null;

    public ?int $fixedDriverId = null;

    public ?int $fixedRouteId = null;

    public string $fixedOfficialName = '';

    public string $fixedDestination = '';

    public string $fixedPurpose = '';

    public ?int $fixedOutOdometer = null;

    public ?int $fixedInOdometer = null;

    // Common feedback
    public ?string $feedbackMessage = null;

    public ?string $feedbackType = 'success'; // 'success' or 'warning'

    public ?float $anomalyVariance = null;

    public function mount(): void
    {
        // Default select latest trip for trips tab
        $activeTrip = TripRequest::whereIn('status', ['HOD_APPROVED', 'DISPATCHED', 'GATE_OUT', 'IN_TRIP'])
            ->latest()
            ->first();

        if ($activeTrip) {
            $this->selectTrip($activeTrip->id);
        }

        // Default select first fixed/dedicated vehicle
        $firstFixed = Vehicle::where(function ($q) {
            $q->where('usage_category', '!=', 'GENERAL_POOL')
                ->orWhereNotNull('dedicated_to_official')
                ->orWhere('vehicle_type', 'STAFF_BUS');
        })->first();

        if ($firstFixed) {
            $this->selectFixedVehicle($firstFixed->id);
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->feedbackMessage = null;
        $this->searchQuery = '';
    }

    /* ----------------------------------------------------
     | Tab 1: Request-Based Trips
     * ---------------------------------------------------- */
    public function selectTrip(int $id): void
    {
        $this->selectedTripId = $id;
        $trip = TripRequest::with(['vehicle', 'driver'])->find($id);

        if ($trip) {
            $this->start_odometer = $trip->start_odometer ?? $trip->vehicle?->current_odometer;
            $this->end_odometer = $trip->end_odometer;
            $this->feedbackMessage = null;
            $this->anomalyVariance = null;
        }
    }

    public function recordGateOut(): void
    {
        $this->validate([
            'start_odometer' => 'required|numeric|min:0',
        ]);

        $trip = TripRequest::with('vehicle')->findOrFail($this->selectedTripId);

        $trip->update([
            'start_odometer' => $this->start_odometer,
            'actual_start_time' => now(),
            'status' => 'GATE_OUT',
        ]);

        // Update vehicle status
        $trip->vehicle?->update([
            'current_odometer' => $this->start_odometer,
            'status' => 'ON_TRIP',
        ]);

        $this->feedbackType = 'success';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? 'গাড়ি সফলভাবে প্রস্থান করেছে (GATE OUT সম্পন্ন)। সময়: '.now()->format('h:i A')
            : 'GATE OUT recorded successfully at '.now()->format('h:i A');
    }

    public function recordGateIn(DistanceAuditService $auditService): void
    {
        $trip = TripRequest::with('vehicle')->findOrFail($this->selectedTripId);

        $this->validate([
            'end_odometer' => [
                'required',
                'numeric',
                'gt:'.($trip->start_odometer ?? 0),
            ],
        ], [
            'end_odometer.gt' => app()->getLocale() === 'bn'
                ? 'প্রত্যাবর্তন ওডোমিটার অবশ্যই শুরুর ওডোমিটার থেকে বেশি হতে হবে।'
                : 'Return odometer must be greater than start odometer.',
        ]);

        $trip->end_odometer = $this->end_odometer;
        $trip->actual_end_time = now();
        $trip->status = 'COMPLETED';
        $trip->save();

        // Run automated distance audit
        $auditService->auditTrip($trip);

        // Update vehicle status & current odometer
        $trip->vehicle?->update([
            'current_odometer' => $this->end_odometer,
            'status' => 'AVAILABLE',
        ]);

        if ($trip->is_distance_anomaly) {
            $this->feedbackType = 'warning';
            $this->anomalyVariance = (float) $trip->distance_variance_percentage;
            $this->feedbackMessage = app()->getLocale() === 'bn'
                ? "⚠️ অস্বাভাবিক দূরত্ব অতিক্রমের সতর্কতা! স্ট্যান্ডার্ড রুট থেকে {$trip->distance_variance_percentage}% অতিরিক্ত দূরত্ব রেকর্ড করা হয়েছে। পরিবহন ইনচার্জকে অবগত করা হয়েছে।"
                : "⚠️ Distance Anomaly Detected! Vehicle traveled {$trip->distance_variance_percentage}% over standard route. Flagged for management audit.";
        } else {
            $this->feedbackType = 'success';
            $this->feedbackMessage = app()->getLocale() === 'bn'
                ? 'গাড়ি সফলভাবে ফ্যাক্টরিতে প্রত্যাবর্তন করেছে (GATE IN সম্পন্ন)। দূরত্ব স্বাভাবিক ও যাচাইকৃত।'
                : 'GATE IN recorded successfully. Mileage verified as normal.';
        }
    }

    /* ----------------------------------------------------
     | Tab 2: Fixed Commute & Management Vehicles
     * ---------------------------------------------------- */
    public function selectFixedVehicle(int $vehicleId): void
    {
        $this->selectedFixedVehicleId = $vehicleId;
        $vehicle = Vehicle::with(['drivers', 'fixedRoutes'])->find($vehicleId);

        if (! $vehicle) {
            return;
        }

        $this->feedbackMessage = null;

        // Check if there is an active OUT log for this vehicle
        $activeLog = VehicleGateLog::where('vehicle_id', $vehicleId)
            ->where('status', 'OUT')
            ->latest('id')
            ->first();

        if ($activeLog) {
            $this->activeGateLogId = $activeLog->id;
            $this->fixedOutOdometer = $activeLog->out_odometer;
            $this->fixedDriverId = $activeLog->driver_id;
            $this->fixedRouteId = $activeLog->fixed_route_id;
            $this->fixedOfficialName = $activeLog->official_name ?? '';
            $this->fixedDestination = $activeLog->destination ?? '';
            $this->fixedPurpose = $activeLog->purpose ?? '';
            $this->fixedInOdometer = null;
        } else {
            $this->activeGateLogId = null;
            $this->fixedOutOdometer = $vehicle->current_odometer;
            $this->fixedDriverId = $vehicle->drivers()->first()?->id;
            $this->fixedOfficialName = $vehicle->dedicated_to_official ?? '';

            $firstRoute = $vehicle->fixedRoutes()->first();
            $this->fixedRouteId = $firstRoute?->id;
            $this->fixedDestination = $firstRoute ? $firstRoute->destination_name : '';

            if ($vehicle->isStaffBus()) {
                $this->fixedPurpose = 'স্টাফ আনা-নেওয়া / Regular Staff Commute';
            } elseif ($vehicle->isDedicated()) {
                $this->fixedPurpose = 'অফিসিয়াল যাতায়াত / Executive Management Commute';
            } else {
                $this->fixedPurpose = 'নিয়মিত ফ্যাক্টরি ডিউটি';
            }

            $this->fixedInOdometer = null;
        }
    }

    public function recordFixedGateOut(): void
    {
        $this->validate([
            'selectedFixedVehicleId' => 'required|exists:vehicles,id',
            'fixedOutOdometer' => 'required|numeric|min:0',
        ]);

        $vehicle = Vehicle::with('fixedRoutes')->findOrFail($this->selectedFixedVehicleId);

        $logType = 'DEDICATED_MANAGEMENT_CAR';
        if ($vehicle->isStaffBus() || $this->fixedRouteId) {
            $logType = 'STAFF_COMMUTE_BUS';
        } elseif ($vehicle->usage_category === 'FACTORY_LOGISTICS') {
            $logType = 'OFFICIAL_DUTY';
        }

        $log = VehicleGateLog::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $this->fixedDriverId,
            'fixed_route_id' => $this->fixedRouteId,
            'log_type' => $logType,
            'log_date' => now()->toDateString(),
            'gate_out_time' => now(),
            'out_odometer' => $this->fixedOutOdometer,
            'official_name' => $this->fixedOfficialName ?: $vehicle->dedicated_to_official,
            'destination' => $this->fixedDestination,
            'purpose' => $this->fixedPurpose,
            'status' => 'OUT',
            'security_guard_id' => auth()->id(),
        ]);

        // Update vehicle status & current odometer
        $vehicle->update([
            'current_odometer' => $this->fixedOutOdometer,
            'status' => 'ON_TRIP',
        ]);

        $this->feedbackType = 'success';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? "গাড়ি ({$vehicle->registration_no}) সফলভাবে প্রস্থান করেছে (GATE OUT সম্পন্ন)। সময়: ".now()->format('h:i A')
            : "Vehicle ({$vehicle->registration_no}) GATE OUT recorded successfully at ".now()->format('h:i A');

        $this->selectFixedVehicle($vehicle->id);
    }

    public function recordFixedGateIn(): void
    {
        if (! $this->activeGateLogId) {
            return;
        }

        $log = VehicleGateLog::with('vehicle')->findOrFail($this->activeGateLogId);

        $this->validate([
            'fixedInOdometer' => [
                'required',
                'numeric',
                'gte:'.($log->out_odometer ?? 0),
            ],
        ], [
            'fixedInOdometer.gte' => app()->getLocale() === 'bn'
                ? 'প্রবেশ ওডোমিটার অবশ্যই প্রস্থান ওডোমিটার থেকে বেশি বা সমান হতে হবে।'
                : 'Arrival odometer must be greater than or equal to departure odometer.',
        ]);

        $totalKm = max(0, $this->fixedInOdometer - ($log->out_odometer ?? 0));

        $log->update([
            'gate_in_time' => now(),
            'in_odometer' => $this->fixedInOdometer,
            'total_km' => $totalKm,
            'status' => 'COMPLETED',
        ]);

        // Update vehicle
        $log->vehicle?->update([
            'current_odometer' => $this->fixedInOdometer,
            'status' => 'AVAILABLE',
        ]);

        $this->feedbackType = 'success';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? "গাড়ি ({$log->vehicle?->registration_no}) সফলভাবে ফ্যাক্টরিতে প্রবেশ করেছে (GATE IN সম্পন্ন)। মোট ভ্রমণ: {$totalKm} কিমি।"
            : "Vehicle ({$log->vehicle?->registration_no}) GATE IN recorded successfully. Total run: {$totalKm} KM.";

        $this->selectFixedVehicle($log->vehicle_id);
    }

    public function render(): View
    {
        // 1. Trips Query
        $selectedTrip = null;
        if ($this->selectedTripId) {
            $selectedTrip = TripRequest::with(['vehicle', 'driver', 'requester', 'factoryUnit'])->find($this->selectedTripId);
        }

        $tripsQuery = TripRequest::with(['vehicle', 'driver'])
            ->whereIn('status', ['HOD_APPROVED', 'DISPATCHED', 'GATE_OUT', 'IN_TRIP', 'COMPLETED'])
            ->latest();

        if ($this->activeTab === 'trips' && ! empty($this->searchQuery)) {
            $query = '%'.trim($this->searchQuery).'%';
            $tripsQuery->where(function ($q) use ($query) {
                $q->where('request_no', 'like', $query)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('registration_no', 'like', $query))
                    ->orWhereHas('driver', fn ($d) => $d->where('name', 'like', $query)->orWhere('office_id_card', 'like', $query));
            });
        }
        $activeGateTrips = $tripsQuery->take(10)->get();

        // 2. Fixed & Dedicated Vehicles Query
        $fixedVehiclesQuery = Vehicle::with(['drivers', 'fixedRoutes'])
            ->where(function ($q) {
                $q->where('usage_category', '!=', 'GENERAL_POOL')
                    ->orWhereNotNull('dedicated_to_official')
                    ->orWhere('vehicle_type', 'STAFF_BUS');
            });

        if ($this->activeTab === 'fixed' && ! empty($this->searchQuery)) {
            $query = '%'.trim($this->searchQuery).'%';
            $fixedVehiclesQuery->where(function ($q) use ($query) {
                $q->where('registration_no', 'like', $query)
                    ->orWhere('dedicated_to_official', 'like', $query)
                    ->orWhereHas('drivers', fn ($d) => $d->where('name', 'like', $query)->orWhere('office_id_card', 'like', $query))
                    ->orWhereHas('fixedRoutes', fn ($r) => $r->where('route_name', 'like', $query));
            });
        }
        $fixedVehicles = $fixedVehiclesQuery->get();

        $selectedFixedVehicle = null;
        if ($this->selectedFixedVehicleId) {
            $selectedFixedVehicle = Vehicle::with(['drivers', 'fixedRoutes', 'factoryUnit'])->find($this->selectedFixedVehicleId);
        }

        // Today's gate logs for fixed vehicles
        $todayGateLogs = VehicleGateLog::with(['vehicle', 'driver', 'fixedRoute'])
            ->whereDate('log_date', now()->toDateString())
            ->latest()
            ->take(15)
            ->get();

        $allDrivers = Driver::where('is_active', true)->orderBy('name')->get();
        $allFixedRoutes = FixedRoute::where('is_active', true)->orderBy('route_name')->get();

        return view('livewire.portal.gate-pass-terminal', [
            'selectedTrip' => $selectedTrip,
            'activeGateTrips' => $activeGateTrips,
            'fixedVehicles' => $fixedVehicles,
            'selectedFixedVehicle' => $selectedFixedVehicle,
            'todayGateLogs' => $todayGateLogs,
            'allDrivers' => $allDrivers,
            'allFixedRoutes' => $allFixedRoutes,
        ]);
    }
}
