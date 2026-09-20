<?php

namespace App\Services\Mobile;

use App\Models\Driver;
use App\Models\FixedRoute;
use App\Models\FuelLog;
use App\Models\MobileSyncOutbox;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequest;
use App\Models\Vehicle;
use App\Models\VehicleGateLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineSyncService
{
    public function __construct(
        protected GeofenceVerificationService $geofenceService
    ) {}

    /**
     * Ingest and process a batch of offline outbox action payloads from a mobile device
     */
    public function processBatchOutbox(array $items, string $deviceId, ?int $driverId = null): array
    {
        $results = [];

        foreach ($items as $item) {
            $idempotencyKey = $item['idempotency_key'] ?? null;
            $actionType = $item['action_type'] ?? null;
            $payload = $item['payload'] ?? [];
            $clientRecordedAt = isset($item['client_recorded_at'])
                ? Carbon::parse($item['client_recorded_at'])
                : now();

            if (! $idempotencyKey || ! $actionType) {
                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'REJECTED',
                    'message' => 'Missing idempotency_key or action_type',
                ];

                continue;
            }

            // Check if already processed (Idempotency guarantee)
            $existing = MobileSyncOutbox::where('idempotency_key', $idempotencyKey)->first();
            if ($existing && $existing->status === 'SYNCED') {
                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'ALREADY_SYNCED',
                    'server_entity_id' => $existing->server_entity_id,
                    'synced_at' => $existing->synced_at?->toIso8601String(),
                ];

                continue;
            }

            $outbox = $existing ?? MobileSyncOutbox::create([
                'device_id' => $deviceId,
                'driver_id' => $driverId ?? ($payload['driver_id'] ?? null),
                'idempotency_key' => $idempotencyKey,
                'action_type' => $actionType,
                'payload' => $payload,
                'client_recorded_at' => $clientRecordedAt,
                'status' => 'PENDING',
            ]);

            try {
                DB::beginTransaction();

                $serverEntityId = match ($actionType) {
                    'GATE_OUT' => $this->handleGateOut($payload, $driverId, $clientRecordedAt),
                    'GATE_IN' => $this->handleGateIn($payload, $driverId, $clientRecordedAt),
                    'FUEL_REFILL' => $this->handleFuelRefill($payload, $driverId, $clientRecordedAt),
                    'EXPENSE_LOG' => $this->handleExpenseLog($payload, $driverId),
                    'TRIP_START' => $this->handleTripStart($payload, $clientRecordedAt),
                    'TRIP_END' => $this->handleTripEnd($payload, $clientRecordedAt),
                    'GPS_BREADCRUMB' => $this->handleGpsBreadcrumb($payload, $driverId, $clientRecordedAt),
                    default => throw new Exception("Unsupported action type: {$actionType}"),
                };

                $outbox->markSynced($serverEntityId);
                DB::commit();

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'SYNCED',
                    'server_entity_id' => $serverEntityId,
                    'synced_at' => now()->toIso8601String(),
                ];
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("OfflineSync failed for key {$idempotencyKey}: {$e->getMessage()}", [
                    'item' => $item,
                ]);

                $outbox->markFailed($e->getMessage());

                $results[] = [
                    'idempotency_key' => $idempotencyKey,
                    'status' => 'FAILED',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Generate bootstrap data packet for device offline cache
     */
    public function generateDevicePullPayload(string $deviceId, ?int $driverId = null): array
    {
        $driver = $driverId ? Driver::with('currentVehicle')->find($driverId) : null;
        $vehicle = $driver?->currentVehicle;

        // Active Trip Requests
        $activeTrips = TripRequest::whereIn('status', ['DISPATCHED', 'GATE_OUT', 'IN_TRIP'])
            ->when($driverId, fn ($q) => $q->where('driver_id', $driverId))
            ->get();

        // Fixed Routes
        $fixedRoutes = FixedRoute::where('is_active', true)
            ->with(['assignedVehicle', 'assignedDriver'])
            ->get();

        // Fuel Quota Info
        $quotaData = null;
        if ($vehicle && $vehicle->monthly_fuel_quota_liters) {
            $quotaData = [
                'vehicle_registration' => $vehicle->registration_no,
                'monthly_quota_liters' => (float) $vehicle->monthly_fuel_quota_liters,
                'consumed_liters' => $vehicle->monthlyFuelConsumedLiters(),
                'remaining_liters' => $vehicle->monthlyFuelQuotaRemaining(),
                'usage_percent' => $vehicle->monthlyFuelQuotaUsagePercent(),
            ];
        }

        return [
            'device_id' => $deviceId,
            'server_timestamp' => now()->toIso8601String(),
            'driver' => $driver ? [
                'id' => $driver->id,
                'name' => $driver->name,
                'office_id_card' => $driver->office_id_card,
                'phone' => $driver->phone,
                'vehicle_id' => $vehicle?->id,
                'vehicle_registration' => $vehicle?->registration_no,
            ] : null,
            'quota' => $quotaData,
            'active_trips' => $activeTrips,
            'fixed_routes' => $fixedRoutes,
            'geofences' => $this->geofenceService->getOfficialGeofences(),
        ];
    }

    protected function handleGateOut(array $payload, ?int $driverId, Carbon $recordedAt): int
    {
        $vehicle = Vehicle::findOrFail($payload['vehicle_id']);
        $outOdometer = (int) ($payload['out_odometer'] ?? $vehicle->current_odometer);

        $log = VehicleGateLog::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $payload['driver_id'] ?? $driverId,
            'fixed_route_id' => $payload['fixed_route_id'] ?? null,
            'log_type' => $payload['log_type'] ?? ($vehicle->isDedicated() ? 'DEDICATED_MANAGEMENT_CAR' : 'STAFF_COMMUTE_BUS'),
            'log_date' => $recordedAt->toDateString(),
            'gate_out_time' => $recordedAt,
            'out_odometer' => $outOdometer,
            'official_name' => $payload['official_name'] ?? $vehicle->dedicated_to_official,
            'destination' => $payload['destination'] ?? 'Factory Route',
            'purpose' => $payload['purpose'] ?? 'Official Duty',
            'status' => 'OUT',
        ]);

        $vehicle->update(['status' => 'ON_TRIP']);

        return $log->id;
    }

    protected function handleGateIn(array $payload, ?int $driverId, Carbon $recordedAt): int
    {
        $vehicle = Vehicle::findOrFail($payload['vehicle_id']);
        $inOdometer = (int) $payload['in_odometer'];

        $activeLog = VehicleGateLog::where('vehicle_id', $vehicle->id)
            ->where('status', 'OUT')
            ->latest('id')
            ->first();

        if ($activeLog) {
            $totalKm = max(0, $inOdometer - (int) $activeLog->out_odometer);
            $activeLog->update([
                'gate_in_time' => $recordedAt,
                'in_odometer' => $inOdometer,
                'total_km' => $totalKm,
                'status' => 'COMPLETED',
            ]);
            $logId = $activeLog->id;
        } else {
            // Direct punch IN
            $log = VehicleGateLog::create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $payload['driver_id'] ?? $driverId,
                'log_type' => $payload['log_type'] ?? 'OTHER',
                'log_date' => $recordedAt->toDateString(),
                'gate_in_time' => $recordedAt,
                'in_odometer' => $inOdometer,
                'out_odometer' => $inOdometer,
                'total_km' => 0,
                'status' => 'COMPLETED',
            ]);
            $logId = $log->id;
        }

        $vehicle->update([
            'status' => 'AVAILABLE',
            'current_odometer' => $inOdometer,
        ]);

        return $logId;
    }

    protected function handleFuelRefill(array $payload, ?int $driverId, Carbon $recordedAt): int
    {
        $vehicle = Vehicle::findOrFail($payload['vehicle_id']);
        $quantity = (float) $payload['fuel_quantity'];
        $unitPrice = (float) ($payload['unit_price'] ?? 0);
        $totalCost = (float) ($payload['total_cost'] ?? ($quantity * $unitPrice));
        $odometer = (int) ($payload['odometer_reading'] ?? $vehicle->current_odometer);

        $fuelLog = FuelLog::create([
            'trip_request_id' => $payload['trip_request_id'] ?? null,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $payload['driver_id'] ?? $driverId ?? $vehicle->drivers()->first()?->id,
            'fuel_type' => $payload['fuel_type'] ?? $vehicle->fuel_type ?? 'DIESEL',
            'refill_date' => $recordedAt,
            'station_name' => $payload['station_name'] ?? 'Trust Filling Station',
            'odometer_reading' => $odometer,
            'fuel_quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_cost' => $totalCost,
            'payment_method' => $payload['payment_method'] ?? 'PETTY_CASH_ADVANCE',
            'dispenser_photo' => $payload['dispenser_photo'] ?? null,
            'odometer_photo' => $payload['odometer_photo'] ?? null,
            'receipt_memo_photo' => $payload['receipt_memo_photo'] ?? null,
        ]);

        if ($odometer > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $odometer]);
        }

        return $fuelLog->id;
    }

    protected function handleExpenseLog(array $payload, ?int $driverId): int
    {
        $tripRequestId = $payload['trip_request_id'];
        $settlement = TripExpenseSettlement::firstOrNew(['trip_request_id' => $tripRequestId]);

        $settlement->driver_id = $payload['driver_id'] ?? $driverId ?? $settlement->driver_id;
        $settlement->total_toll_expense = (float) ($payload['total_toll_expense'] ?? $settlement->total_toll_expense);
        $settlement->total_parking_expense = (float) ($payload['total_parking_expense'] ?? $settlement->total_parking_expense);
        $settlement->total_driver_food_allowance = (float) ($payload['total_driver_food_allowance'] ?? $settlement->total_driver_food_allowance);
        $settlement->total_emergency_repair_expense = (float) ($payload['total_emergency_repair_expense'] ?? $settlement->total_emergency_repair_expense);
        $settlement->total_other_expense = (float) ($payload['total_other_expense'] ?? $settlement->total_other_expense);

        $settlement->total_actual_expense = (
            $settlement->total_fuel_expense +
            $settlement->total_toll_expense +
            $settlement->total_parking_expense +
            $settlement->total_driver_food_allowance +
            $settlement->total_emergency_repair_expense +
            $settlement->total_other_expense
        );

        $settlement->status = $payload['status'] ?? 'DRAFT_BY_DRIVER';
        $settlement->save();

        return $settlement->id;
    }

    protected function handleTripStart(array $payload, Carbon $recordedAt): int
    {
        $trip = TripRequest::findOrFail($payload['trip_request_id']);
        $startOdo = (int) ($payload['start_odometer'] ?? $trip->vehicle?->current_odometer);

        $trip->update([
            'start_odometer' => $startOdo,
            'actual_start_time' => $recordedAt,
            'status' => 'GATE_OUT',
        ]);

        $trip->vehicle?->update(['status' => 'ON_TRIP']);

        return $trip->id;
    }

    protected function handleTripEnd(array $payload, Carbon $recordedAt): int
    {
        $trip = TripRequest::findOrFail($payload['trip_request_id']);
        $endOdo = (int) $payload['end_odometer'];
        $claimedKm = max(0, $endOdo - (int) $trip->start_odometer);

        $trip->update([
            'end_odometer' => $endOdo,
            'actual_end_time' => $recordedAt,
            'claimed_distance_km' => $claimedKm,
            'status' => 'COMPLETED',
        ]);

        $trip->vehicle?->update([
            'status' => 'AVAILABLE',
            'current_odometer' => $endOdo,
        ]);

        return $trip->id;
    }

    protected function handleGpsBreadcrumb(array $payload, ?int $driverId, Carbon $recordedAt): int
    {
        $ping = $this->geofenceService->recordPing(
            vehicleId: (int) $payload['vehicle_id'],
            latitude: (float) $payload['latitude'],
            longitude: (float) $payload['longitude'],
            driverId: $payload['driver_id'] ?? $driverId,
            tripRequestId: $payload['trip_request_id'] ?? null,
            speedKmh: (float) ($payload['speed_kmh'] ?? 0),
            heading: isset($payload['heading']) ? (float) $payload['heading'] : null,
            accuracyMeters: (float) ($payload['accuracy_meters'] ?? 0),
            batteryLevel: isset($payload['battery_level']) ? (int) $payload['battery_level'] : null,
            isMockLocation: (bool) ($payload['is_mock_location'] ?? false),
            recordedAt: $recordedAt
        );

        return $ping->id;
    }
}
