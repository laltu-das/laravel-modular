<?php

declare(strict_types=1);

namespace Modules\Catalog\Observers;

use Modules\Catalog\Models\Product;

final class ProductObserver
{
    public function created(Product $product): void
    {
        app()->instance('catalog.product_created', $product->getKey());
    }
}
