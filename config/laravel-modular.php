<?php

declare(strict_types=1);

return [
    'placeholder' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Module location
    |--------------------------------------------------------------------------
    |
    | Every directory directly below "path" is a module, namespaced below
    | "namespace" (Spring's package-by-feature, but for Laravel).
    |
    */
    'enabled' => true,
    'path' => base_path('Modules'),
    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Multi-tenancy
    |--------------------------------------------------------------------------
    |
    | tenant_resolver: class implementing TenantResolver, returning the current
    | tenant (wire your existing TenantContext here). tenant_voter: optional
    | class implementing TenantModuleVoter that enables/disables modules per
    | tenant, similar to Spring profiles.
    |
    */
    'tenant_resolver' => null,
    'tenant_voter' => null,

    /*
    |--------------------------------------------------------------------------
    | Module boundaries (public API)
    |--------------------------------------------------------------------------
    |
    | A module's public API is made of these top-level directories; every other
    | top-level directory is internal by default. `php artisan module:boundaries`
    | reports cross-module references to internal classes.
    |
    */
    'public_directories' => ['Contracts', 'Events', 'Enums'],

    /*
    |--------------------------------------------------------------------------
    | Auto-discovery
    |--------------------------------------------------------------------------
    |
    | Convention over configuration, like Spring Boot auto-configuration. Turn
    | any aspect off per application; everything is enabled by default.
    |
    */
    'auto_discovery' => [
        'config' => true,        // {Module}/config/*.php merged under the file name
        'commands' => true,      // {Module}/Console/Commands/* registered in console
        'listeners' => true,     // {Module}/Listeners/* wired from the handle() type-hint
        'migrations' => true,    // {Module}/database/migrations
        'observers' => true,     // {Module}/Observers/{Model}Observer => Models/{Model}
        'policies' => true,      // {Module}/Policies/{Model}Policy => Models/{Model}
        'providers' => true,     // {Module}/Providers/*ServiceProvider
        'routes' => true,        // {Module}/routes/web.php and routes/api.php
        'translations' => true,  // {Module}/resources/lang (namespaced by module)
        'views' => true,         // {Module}/resources/views (namespaced by module)
    ],



    /*
    |--------------------------------------------------------------------------
    | API Features
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'api',
        'version' => 'v1',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Caching
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'default_store' => 'default',
        'ttl' => 3600,
        'tags_enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Middleware Stacks
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'enabled' => true,
        'global_stack' => ['api', 'throttle:api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Broadcasting
    |--------------------------------------------------------------------------
    */
    'broadcasting' => [
        'enabled' => false,
        'default_channel' => 'modular',
    ],
];
