<?php

namespace App\Livewire\Portal;

use App\Models\Driver;
use App\Models\MobileSyncOutbox;
use App\Models\Vehicle;
use App\Services\Mobile\GeofenceVerificationService;
use App\Services\Mobile\ImageWatermarkService;
use App\Services\Mobile\OfflineSyncService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class MobileTerminal extends Component
{
    use WithFileUploads;

    public string $deviceId = 'MOB-DEFAULT';

    public ?int $selectedDriverId = null;

    public ?int $selectedVehicleId = null;

    public string $activeAction = 'OVERVIEW'; // OVERVIEW, GATE_OUT, GATE_IN, FUEL, EXPENSE, CAMERA

    // Form states
    public ?int $odometerReading = null;

    public ?string $destination = null;

    public ?string $purpose = null;

    public ?float $fuelQuantity = null;

    public ?float $fuelUnitPrice = null;

    public ?float $fuelTotalCost = null;

    public string $stationName = 'Trust Filling Station';

    public ?float $tollExpense = null;

    public ?float $parkingExpense = null;

    public ?float $foodAllowance = null;

    // GPS states
    public ?float $currentLatitude = null;

    public ?float $currentLongitude = null;

    public ?string $nearestGeofenceName = null;

    public bool $isWithinGeofence = false;

    // Camera upload
    public $capturedPhoto = null;

    public ?string $watermarkedPhotoUrl = null;

    // Feedback
    public ?string $feedbackMessage = null;

    public string $feedbackType = 'info';

    public function mount(GeofenceVerificationService $geofenceService): void
    {
        // Default device ID if none set
        $this->deviceId = session('mobile_device_id', 'MOB-'.strtoupper(substr(md5(request()->userAgent() ?? 'agent'), 0, 8)));

        // Default to first driver if available
        $firstDriver = Driver::first();
        if ($firstDriver) {
            $this->selectedDriverId = $firstDriver->id;
            $this->selectedVehicleId = $firstDriver->current_vehicle_id ?? Vehicle::first()?->id;
        } else {
            $this->selectedVehicleId = Vehicle::first()?->id;
        }

        // Default coordinates to BK Bari Plant if not fetched from client yet
        $defaultGeo = $geofenceService->getOfficialGeofences()['GZP'] ?? null;
        if ($defaultGeo) {
            $this->currentLatitude = $defaultGeo['lat'];
            $this->currentLongitude = $defaultGeo['lng'];
            $this->nearestGeofenceName = $defaultGeo['name'];
            $this->isWithinGeofence = true;
        }
    }

    public function updateGpsCoordinates(float $lat, float $lng, GeofenceVerificationService $geofenceService): void
    {
        $this->currentLatitude = $lat;
        $this->currentLongitude = $lng;

        $verification = $geofenceService->verifyPing($lat, $lng);
        $this->nearestGeofenceName = $verification['nearest_geofence_name'];
        $this->isWithinGeofence = $verification['is_within_geofence'];
    }

    public function submitGateOut(OfflineSyncService $syncService): void
    {
        $this->validate([
            'selectedVehicleId' => 'required|exists:vehicles,id',
            'odometerReading' => 'required|numeric|min:0',
        ]);

        $item = [
            'idempotency_key' => 'OUT-'.uniqid().'-'.now()->timestamp,
            'action_type' => 'GATE_OUT',
            'payload' => [
                'vehicle_id' => $this->selectedVehicleId,
                'driver_id' => $this->selectedDriverId,
                'out_odometer' => (int) $this->odometerReading,
                'destination' => $this->destination ?? 'Factory Transit',
                'purpose' => $this->purpose ?? 'General Duty',
            ],
            'client_recorded_at' => now()->toIso8601String(),
        ];

        $results = $syncService->processBatchOutbox([$item], $this->deviceId, $this->selectedDriverId);
        $this->feedbackType = $results[0]['status'] === 'SYNCED' ? 'success' : 'warning';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? 'গেট আউট সফলভাবে রেকর্ড হয়েছে।'
            : 'Gate Out successfully recorded.';

        $this->activeAction = 'OVERVIEW';
        $this->reset(['odometerReading', 'destination', 'purpose']);
    }

    public function submitGateIn(OfflineSyncService $syncService): void
    {
        $this->validate([
            'selectedVehicleId' => 'required|exists:vehicles,id',
            'odometerReading' => 'required|numeric|min:0',
        ]);

        $item = [
            'idempotency_key' => 'IN-'.uniqid().'-'.now()->timestamp,
            'action_type' => 'GATE_IN',
            'payload' => [
                'vehicle_id' => $this->selectedVehicleId,
                'driver_id' => $this->selectedDriverId,
                'in_odometer' => (int) $this->odometerReading,
            ],
            'client_recorded_at' => now()->toIso8601String(),
        ];

        $results = $syncService->processBatchOutbox([$item], $this->deviceId, $this->selectedDriverId);
        $this->feedbackType = $results[0]['status'] === 'SYNCED' ? 'success' : 'warning';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? 'গেট ইন ও দূরত্ব সফলভাবে আপডেট হয়েছে।'
            : 'Gate In and run KM successfully updated.';

        $this->activeAction = 'OVERVIEW';
        $this->reset(['odometerReading']);
    }

    public function processCameraCapture(ImageWatermarkService $watermarkService): void
    {
        $this->validate([
            'capturedPhoto' => 'required|image|max:10240',
            'selectedVehicleId' => 'required|exists:vehicles,id',
        ]);

        $vehicle = Vehicle::find($this->selectedVehicleId);
        $driver = Driver::find($this->selectedDriverId);

        $path = $watermarkService->applyWatermark(
            imageFile: $this->capturedPhoto,
            vehicleReg: $vehicle?->registration_no ?? 'UNKNOWN-VEHICLE',
            driverName: $driver?->name,
            latitude: $this->currentLatitude,
            longitude: $this->currentLongitude,
            locationName: $this->nearestGeofenceName
        );

        $this->watermarkedPhotoUrl = asset('storage/'.$path);
        $this->feedbackType = 'success';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? 'ছবিটি স্বয়ংক্রিয় ওয়াটারমার্ক সহ সুরক্ষিতভাবে সেভ হয়েছে।'
            : 'Photo saved with tamper-evident watermark seal.';
    }

    public function syncPendingOutbox(OfflineSyncService $syncService): void
    {
        $pending = MobileSyncOutbox::byDevice($this->deviceId)
            ->pending()
            ->get();

        if ($pending->isEmpty()) {
            $this->feedbackMessage = app()->getLocale() === 'bn'
                ? 'সিঙ্ক করার মতো কোনো অপেক্ষমাণ আউটবক্স রেকর্ড নেই।'
                : 'No pending offline items to sync.';
            $this->feedbackType = 'info';

            return;
        }

        $items = $pending->map(fn ($o) => [
            'idempotency_key' => $o->idempotency_key,
            'action_type' => $o->action_type,
            'payload' => $o->payload,
            'client_recorded_at' => $o->client_recorded_at->toIso8601String(),
        ])->toArray();

        $results = $syncService->processBatchOutbox($items, $this->deviceId, $this->selectedDriverId);
        $this->feedbackType = 'success';
        $this->feedbackMessage = app()->getLocale() === 'bn'
            ? count($results).' টি অফলাইন রেকর্ড সফলভাবে সার্ভারে সিঙ্ক হয়েছে।'
            : count($results).' offline records successfully synced.';
    }

    public function render(OfflineSyncService $syncService)
    {
        $vehicles = Vehicle::where('is_active', true)->get();
        $drivers = Driver::where('is_active', true)->get();

        $selectedVehicle = $this->selectedVehicleId ? Vehicle::find($this->selectedVehicleId) : null;
        $activeGateLog = $selectedVehicle?->activeGateLog();

        $outboxCount = MobileSyncOutbox::byDevice($this->deviceId)->pending()->count();
        $recentOutbox = MobileSyncOutbox::byDevice($this->deviceId)->latest()->take(5)->get();

        return view('livewire.portal.mobile-terminal', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'selectedVehicle' => $selectedVehicle,
            'activeGateLog' => $activeGateLog,
            'outboxCount' => $outboxCount,
            'recentOutbox' => $recentOutbox,
        ]);
    }
}
