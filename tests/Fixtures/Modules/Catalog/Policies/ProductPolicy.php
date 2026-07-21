<?php

declare(strict_types=1);

namespace Modules\Catalog\Policies;

final class ProductPolicy
{
    public function view(): bool
    {
        return true;
    }
}
