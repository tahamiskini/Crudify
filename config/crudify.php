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

    /*
    |--------------------------------------------------------------------------
    | Auto Sync Parent Relations
    |--------------------------------------------------------------------------
    |
    | Automatically resolve parent IDs for HasMany relations based on
    | the relationship hierarchy.
    |
    */
    'auto_sync_parent_relations' => false,

    /*
    |--------------------------------------------------------------------------
    | Sync Parent Relations Max Depth
    |--------------------------------------------------------------------------
    |
    | Maximum depth for recursive parent relation resolution.
    |
    */
    'sync_parent_relations_max_depth' => 5,
];
