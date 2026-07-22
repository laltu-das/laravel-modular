<?php

declare(strict_types=1);

namespace Laltu\Modular\Testing;

final class ModuleMockContracts
{
    private array $mocks = [];

    public function mock(string $contract, object $implementation): static
    {
        $this->mocks[$contract] = $implementation;
        app()->bind($contract, fn () => $implementation);
        return $this;
    }

    public function restore(): static
    {
        $this->mocks = [];
        return $this;
    }
}
