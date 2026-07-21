<?php

declare(strict_types=1);

return [
    'placeholder' => 'default',
    'path' => base_path('Domains'),
    'namespace' => 'Domains',
    'enabled' => true,
    'tenant_resolver' => null,
    'public_directories' => ['Application', 'Domain', 'Contracts'],
];
