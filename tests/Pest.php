<?php

declare(strict_types=1);

use LaravelModular\LaravelModular\Tests\Concerns\WithoutAutoDiscovery;
use LaravelModular\LaravelModular\Tests\Concerns\WithTenancy;
use LaravelModular\LaravelModular\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
uses(WithTenancy::class)->in('Feature/Tenancy');
uses(WithoutAutoDiscovery::class)->in('Feature/WithoutDiscovery');
