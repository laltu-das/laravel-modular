<?php

declare(strict_types=1);

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/Concerns/WithTenancy.php';
require_once __DIR__ . '/Concerns/WithoutAutoDiscovery.php';

use Laltu\Modular\Tests\Concerns\WithoutAutoDiscovery;
use Laltu\Modular\Tests\Concerns\WithTenancy;
use Laltu\Modular\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
uses(WithTenancy::class)->in('Feature/Tenancy');
uses(WithoutAutoDiscovery::class)->in('Feature/WithoutDiscovery');
