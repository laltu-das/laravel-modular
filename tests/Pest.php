<?php

declare(strict_types=1);

use Laltu\Modular\Tests\Concerns\WithoutAutoDiscovery;
use Laltu\Modular\Tests\Concerns\WithTenancy;
use Laltu\Modular\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
uses(WithTenancy::class)->in('Feature/Tenancy');
uses(WithoutAutoDiscovery::class)->in('Feature/WithoutDiscovery');
