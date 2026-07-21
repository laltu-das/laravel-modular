<?php

declare(strict_types=1);

use Laltu\LaravelModular\Tests\Concerns\WithoutAutoDiscovery;
use Laltu\LaravelModular\Tests\Concerns\WithTenancy;
use Laltu\LaravelModular\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
uses(WithTenancy::class)->in('Feature/Tenancy');
uses(WithoutAutoDiscovery::class)->in('Feature/WithoutDiscovery');
