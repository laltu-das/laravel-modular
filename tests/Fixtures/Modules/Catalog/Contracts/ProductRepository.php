<?php

declare(strict_types=1);

namespace Modules\Catalog\Contracts;

interface ProductRepository
{
    public function skuExists(string $sku): bool;
}
