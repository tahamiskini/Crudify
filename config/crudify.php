<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for your root Laravel application.
    |
    */
    'namespace' => env('CRUDIFY_NAMESPACE', 'App'),

    /*
    |--------------------------------------------------------------------------
    | Routes Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix applied to all auto-generated CRUD routes.
    |
    */
    'routes_prefix' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Middlewares
    |--------------------------------------------------------------------------
    |
    | Global middlewares applied to all CRUD routes.
    |
    */
    'middlewares' => [],

    /*
    |--------------------------------------------------------------------------
    | Merge Model Data to Request
    |--------------------------------------------------------------------------
    |
    | When enabled, existing model data is merged into the request for
    | update operations so validation sees all fields, not just those sent.
    |
    */
    'merge_model_data_to_request' => false,
];
