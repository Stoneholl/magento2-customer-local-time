<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Model\Core;

use Stoneholl\Core\Api\ProductProviderInterface;
use Stoneholl\Core\Model\Data\ProductDescriptor;

class ProductProvider implements ProductProviderInterface
{
    public function getProducts(): array
    {
        return [new ProductDescriptor(
            'customer-local-time',
            'Stoneholl Customer Local Time',
            ['Stoneholl_CustomerLocalTime'],
            ['stoneholl/magento2-customer-local-time']
        )];
    }
}
