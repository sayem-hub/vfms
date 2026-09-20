<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application ID & Identification
    |--------------------------------------------------------------------------
    */
    'app_id' => env('NATIVEPHP_APP_ID', 'com.applifebd.vfms'),
    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),
    'app_name' => env('NATIVEPHP_APP_NAME', 'NZ Group VFMS Mobile'),
    'author' => 'NZ Group Transport & ICT Division',

    /*
    |--------------------------------------------------------------------------
    | Mobile App Entry Route
    |--------------------------------------------------------------------------
    */
    'entry_url' => env('NATIVEPHP_ENTRY_URL', '/portal/mobile'),

    /*
    |--------------------------------------------------------------------------
    | Mobile Device Permissions (Android & iOS)
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'camera' => true,
        'location_fine' => true,
        'location_coarse' => true,
        'network_state' => true,
        'wake_lock' => true,
        'storage_read' => true,
        'storage_write' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Offline Local SQLite Storage Configuration
    |--------------------------------------------------------------------------
    */
    'offline_storage' => [
        'driver' => 'sqlite',
        'database' => database_path('mobile_offline_cache.sqlite'),
        'sync_interval_seconds' => 30,
        'max_retry_attempts' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Factory & Hub Geofence Boundaries (Official Lat/Lng Coordinates)
    |--------------------------------------------------------------------------
    */
    'official_geofences' => [
        'HO' => [
            'name' => 'Corporate Head Office, Baridhara DOHS, Dhaka',
            'lat' => 23.8197,
            'lng' => 90.4143,
            'radius_meters' => 500,
        ],
        'GZP' => [
            'name' => 'BK Bari Factory Plant, Gazipur',
            'lat' => 24.1036,
            'lng' => 90.3991,
            'radius_meters' => 800,
        ],
        'CGZP' => [
            'name' => 'CAKL, Bhobanipur, Gazipur',
            'lat' => 24.1483,
            'lng' => 90.4224,
            'radius_meters' => 800,
        ],
        'AGZP' => [
            'name' => 'BIDC Road, Joydebpur, Gazipur',
            'lat' => 23.9310,
            'lng' => 90.2690,
            'radius_meters' => 600,
        ],
        'CTG' => [
            'name' => 'Chittagong Port Off-Dock Depot',
            'lat' => 22.3167,
            'lng' => 91.8000,
            'radius_meters' => 2000,
        ],
        'AIR' => [
            'name' => 'Dhaka Airport Cargo Village',
            'lat' => 23.8433,
            'lng' => 90.4030,
            'radius_meters' => 1000,
        ],
        'YGZP' => [
            'name' => 'NAZ Yarn Store, Rajabari, Gazipur',
            'lat' => 24.1044,
            'lng' => 90.4961,
            'radius_meters' => 600,
        ],
    ],
];
