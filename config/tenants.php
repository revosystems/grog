<?php

return [
    "migration_paths" => [
        "database/migrations/tenants",
        "database/migrations/tenants/stocks",
    ],

    "seed_classes" => [
        TenantConfigSeeder::class,
        TenantProductsSeeder::class,
    ]
];