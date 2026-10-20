<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;

/** Read the chosen order snapshot, never a customer's live address or other address fields. */
class AddressCountryReader
{
    /** @var ResourceConnection */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function getCountry(Order $order, string $source): ?string
    {
        if (!in_array($source, ['shipping', 'billing'], true)) {
            throw new \InvalidArgumentException('Unsupported order address source.');
        }
        $orderId = (int) $order->getEntityId();
        $addressId = (int) $order->getData($source . '_address_id');
        if ($orderId <= 0 || $addressId <= 0) {
            return null;
        }
        // getAddresses()/getShippingAddress() may lazily load all fields; inspect loaded data directly.
        $addresses = $order->getData('addresses');
        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                if ($address instanceof OrderAddressInterface
                    && (int) $address->getEntityId() === $addressId
                    && (int) $address->getParentId() === $orderId
                    && $address->getAddressType() === $source
                ) {
                    if ($address instanceof \Magento\Sales\Model\Order\Address && $address->isDeleted()) {
                        return null;
                    }
                    return (string) $address->getCountryId();
                }
            }
        }
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('sales_order_address'), ['country_id'])
            ->where('entity_id = ?', $addressId)
            ->where('parent_id = ?', $orderId)
            ->where('address_type = ?', $source)
            ->limit(1);
        $row = $connection->fetchRow($select);
        return $row === false ? null : (string) $row['country_id'];
    }
}
