<?php

namespace App\Livewire\Portal;

use App\Models\Company;
use App\Models\FactoryUnit;
use App\Models\TripRequest;
use App\Models\User;
use App\Services\Routing\RoutingManager;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('layouts.app')]
class TripRequestPortal extends Component
{
    #[Rule('required|exists:companies,id')]
    public ?int $company_id = null;

    #[Rule('nullable|exists:factory_units,id')]
    public ?int $factory_unit_id = null;

    #[Rule('required|string')]
    public string $trip_type = 'OFFICIAL_DUTY';

    #[Rule('required|string|min:5')]
    public string $purpose = '';

    #[Rule('required|string')]
    public string $origin_name = '';

    #[Rule('required|string')]
    public string $destination_name = '';

    public ?float $origin_latitude = null;

    public ?float $origin_longitude = null;

    public ?float $destination_latitude = null;

    public ?float $destination_longitude = null;

    #[Rule('required|date|after_or_equal:today')]
    public string $scheduled_start_time = '';

    #[Rule('nullable|date|after_or_equal:scheduled_start_time')]
    public ?string $scheduled_end_time = null;

    public bool $showSuccessModal = false;

    public ?string $createdRequestNo = null;

    public function mount(): void
    {
        $firstCompany = Company::first();
        $this->company_id = $firstCompany?->id;
        $this->factory_unit_id = FactoryUnit::where('company_id', $this->company_id)->first()?->id;
        $this->scheduled_start_time = now()->addHour()->format('Y-m-d\TH:i');
    }

    public function setPresetRoute(string $preset): void
    {
        $locations = [
            'HO' => ['name' => 'Corporate Head Office, Baridhara DOHS, Dhaka', 'lat' => 23.8050, 'lng' => 90.4180],
            'GZP' => ['name' => 'BK Bari Factory Plant, Gazipur', 'lat' => 24.0400, 'lng' => 90.3950],
            'NKG' => ['name' => 'Kachpur Unit 2 Mill, Narayanganj', 'lat' => 23.6850, 'lng' => 90.5120],
            'DEPZ' => ['name' => 'DEPZ Savar Factory Complex', 'lat' => 23.9310, 'lng' => 90.2690],
            'CTG' => ['name' => 'Chittagong Port Off-Dock Depot', 'lat' => 22.3167, 'lng' => 91.8000],
            'AIR' => ['name' => 'Dhaka Airport Cargo Village', 'lat' => 23.8433, 'lng' => 90.4030],
        ];

        if ($preset === 'HO_TO_GZP') {
            $this->origin_name = $locations['HO']['name'];
            $this->origin_latitude = $locations['HO']['lat'];
            $this->origin_longitude = $locations['HO']['lng'];
            $this->destination_name = $locations['GZP']['name'];
            $this->destination_latitude = $locations['GZP']['lat'];
            $this->destination_longitude = $locations['GZP']['lng'];
        } elseif ($preset === 'GZP_TO_CTG') {
            $this->origin_name = $locations['GZP']['name'];
            $this->origin_latitude = $locations['GZP']['lat'];
            $this->origin_longitude = $locations['GZP']['lng'];
            $this->destination_name = $locations['CTG']['name'];
            $this->destination_latitude = $locations['CTG']['lat'];
            $this->destination_longitude = $locations['CTG']['lng'];
        } elseif ($preset === 'HO_TO_NKG') {
            $this->origin_name = $locations['HO']['name'];
            $this->origin_latitude = $locations['HO']['lat'];
            $this->origin_longitude = $locations['HO']['lng'];
            $this->destination_name = $locations['NKG']['name'];
            $this->destination_latitude = $locations['NKG']['lat'];
            $this->destination_longitude = $locations['NKG']['lng'];
        }
    }

    public function submitRequest(RoutingManager $routingManager): void
    {
        $this->validate();

        // Auto calculate expected distance via OSRM / Routing Manager
        $expectedDistance = null;
        if ($this->origin_latitude && $this->origin_longitude && $this->destination_latitude && $this->destination_longitude) {
            $route = $routingManager->calculateDistanceAndDuration(
                originLat: (float) $this->origin_latitude,
                originLng: (float) $this->origin_longitude,
                destLat: (float) $this->destination_latitude,
                destLng: (float) $this->destination_longitude
            );
            if ($route->isSuccessful) {
                $expectedDistance = $route->distanceKm;
            }
        }

        $requesterId = auth()->id() ?? User::first()?->id ?? 1;
        $requestNo = 'TR-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

        TripRequest::create([
            'request_no' => $requestNo,
            'company_id' => $this->company_id,
            'factory_unit_id' => $this->factory_unit_id,
            'requester_id' => $requesterId,
            'trip_type' => $this->trip_type,
            'purpose' => $this->purpose,
            'origin_name' => $this->origin_name,
            'destination_name' => $this->destination_name,
            'origin_latitude' => $this->origin_latitude,
            'origin_longitude' => $this->origin_longitude,
            'destination_latitude' => $this->destination_latitude,
            'destination_longitude' => $this->destination_longitude,
            'scheduled_start_time' => $this->scheduled_start_time,
            'scheduled_end_time' => $this->scheduled_end_time,
            'expected_distance_km' => $expectedDistance,
            'status' => 'SUBMITTED',
        ]);

        $this->createdRequestNo = $requestNo;
        $this->showSuccessModal = true;

        // Reset form
        $this->reset(['purpose', 'origin_name', 'destination_name', 'origin_latitude', 'origin_longitude', 'destination_latitude', 'destination_longitude']);
    }

    public function render(): View
    {
        $companies = Company::where('is_active', true)->get();
        $factoryUnits = FactoryUnit::where('company_id', $this->company_id)->where('is_active', true)->get();
        $recentRequests = TripRequest::with(['vehicle', 'driver', 'factoryUnit'])
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.portal.trip-request-portal', [
            'companies' => $companies,
            'factoryUnits' => $factoryUnits,
            'recentRequests' => $recentRequests,
        ]);
    }
}
