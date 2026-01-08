<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be applied to all routes defined by the InertiaStatamic
    | package, including form submission endpoints. Using a unique prefix
    | helps prevent route collisions with your application's existing routes.
    |
    | You can override this value via the environment variable:
    | INERTIA_STATAMIC_ROUTE_PREFIX
    |
    | Example: 'inertia-statamic'
    |
    */

    'route_prefix' => env('INERTIA_STATAMIC_ROUTE_PREFIX', 'inertia-statamic'),
];
