<?php

declare(strict_types=1);

use LaravelModular\LaravelModular\Tests\NoDiscoveryTestCase;
use LaravelModular\LaravelModular\Tests\TenantTestCase;
use LaravelModular\LaravelModular\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
uses(TenantTestCase::class)->in('Feature/Tenancy');
uses(NoDiscoveryTestCase::class)->in('Feature/WithoutDiscovery');
