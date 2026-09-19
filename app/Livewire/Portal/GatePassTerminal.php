<?php

namespace App\Livewire\Portal;

use App\Models\TripRequest;
use App\Services\Audit\DistanceAuditService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GatePassTerminal extends Component
{
    public string $searchQuery = '';

    public ?int $selectedTripId = null;

    // Gate Out fields
    public ?int $start_odometer = null;

    // Gate In fields
    public ?int $end_odometer = null;

    public ?string $feedbackMessage = null;

    public ?string $feedbackType = 'success'; // 'success' or 'warning'

    public ?float $anomalyVariance = null;

    public function mount(): void
    {
        // Default select latest trip that is dispatched or in trip
        $activeTrip = TripRequest::whereIn('status', ['HOD_APPROVED', 'DISPATCHED', 'GATE_OUT', 'IN_TRIP'])
            ->latest()
            ->first();

        if ($activeTrip) {
            $this->selectTrip($activeTrip->id);
        }
    }

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

    public function render(): View
    {
        $selectedTrip = null;
        if ($this->selectedTripId) {
            $selectedTrip = TripRequest::with(['vehicle', 'driver', 'requester', 'factoryUnit'])->find($this->selectedTripId);
        }

        $tripsQuery = TripRequest::with(['vehicle', 'driver'])
            ->whereIn('status', ['HOD_APPROVED', 'DISPATCHED', 'GATE_OUT', 'IN_TRIP', 'COMPLETED'])
            ->latest();

        if (! empty($this->searchQuery)) {
            $query = '%'.trim($this->searchQuery).'%';
            $tripsQuery->where(function ($q) use ($query) {
                $q->where('request_no', 'like', $query)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('registration_no', 'like', $query))
                    ->orWhereHas('driver', fn ($d) => $d->where('name', 'like', $query)->orWhere('office_id_card', 'like', $query));
            });
        }

        $activeGateTrips = $tripsQuery->take(10)->get();

        return view('livewire.portal.gate-pass-terminal', [
            'selectedTrip' => $selectedTrip,
            'activeGateTrips' => $activeGateTrips,
        ]);
    }
}
