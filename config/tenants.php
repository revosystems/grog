<?php

return [
    "DB_TENANTS_PREFIX" => env('DB_TENANTS_PREFIX','Revo_'),
    "DB_HOST"           => env('DB_HOST'        ,'127.0.0.1'),
    "DB_USERNAME"       => env('DB_USERNAME'    ,'root'),
    "DB_PASSWORD"       => env('DB_PASSWORD'    ,''),

    "migration_paths" => [
        "database/migrations/tenants",
        "database/migrations/tenants/stocks",
    ],

    "seed_classes" => [
        TenantConfigSeeder::class,
        TenantProductsSeeder::class,
    ],
];