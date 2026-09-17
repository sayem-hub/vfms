<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pluggable Routing Engine Configuration
    |--------------------------------------------------------------------------
    | Supported drivers: "osrm" (default, free open-source), "google_maps"
    */
    'routing' => [
        'default' => env('ROUTING_DRIVER', 'osrm'),
        'drivers' => [
            'osrm' => [
                'base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),
                'timeout' => env('OSRM_TIMEOUT', 10),
            ],
            'google_maps' => [
                'api_key' => env('GOOGLE_MAPS_API_KEY', ''),
                'timeout' => env('GOOGLE_MAPS_TIMEOUT', 10),
            ],
        ],
        // Percentage variance between claimed odometer and routed distance to trigger audit alert
        'anomaly_threshold_percentage' => (float) env('ROUTING_ANOMALY_THRESHOLD', 15.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fuel & Efficiency Audit Thresholds
    |--------------------------------------------------------------------------
    | Percentage drop from vehicle baseline expected KM/L to trigger siphon alert
    */
    'fuel' => [
        'drop_alert_percentage' => (float) env('FUEL_DROP_ALERT_PERCENTAGE', 20.0),
        'currency' => env('VFMS_CURRENCY', 'BDT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom ERP Integration Settings
    |--------------------------------------------------------------------------
    */
    'erp' => [
        'enabled' => (bool) env('ERP_INTEGRATION_ENABLED', false),
        'api_url' => env('ERP_API_URL', ''),
        'api_key' => env('ERP_API_KEY', ''),
    ],
];
