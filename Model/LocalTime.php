<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class LocalTime
{
    /** @var ScopeConfigInterface */
    private $config;
    /** @var AuthorizationInterface */
    private $authorization;
    /** @var AddressCountryReader */
    private $reader;
    /** @var Estimate */
    private $estimate;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ScopeConfigInterface $config,
        AuthorizationInterface $authorization,
        AddressCountryReader $reader,
        Estimate $estimate,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->authorization = $authorization;
        $this->reader = $reader;
        $this->estimate = $estimate;
        $this->logger = $logger;
    }

    public function isAllowed(string $feature = 'time_information'): bool
    {
        return $this->config->isSetFlag(
            'stoneholl_customer_local_time/general/' . ($feature === 'order_date' ? 'show_order_date' : 'enabled'),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ) && $this->authorization->isAllowed('Magento_Sales::actions_view')
            && $this->authorization->isAllowed('Stoneholl_CustomerLocalTime::view')
            && ($feature === 'order_date' || $this->showCurrent('customer') || $this->showCurrent('store'));
    }

    public function getState(Order $order, string $feature = 'time_information'): array
    {
        if (!$this->isAllowed($feature)) {
            return ['status' => 'hidden'];
        }
        $orderId = (int) $order->getEntityId();
        if ($orderId <= 0) {
            return ['status' => 'hidden'];
        }
        $showCustomer = $feature === 'order_date' || $this->showCurrent('customer');
        $showStore = $feature !== 'order_date' && $this->showCurrent('store');
        $source = $showCustomer ? ($order->getIsVirtual() ? 'billing' : 'shipping') : null;
        $base = ['order_id' => $orderId, 'source' => $source];
        if ($feature !== 'order_date') {
            $base += ['show_customer_time' => $showCustomer, 'show_store_time' => $showStore];
        }
        try {
            $country = $showCustomer ? $this->reader->getCountry($order, $source) : null;
            return $base + ($feature === 'order_date'
                ? $this->estimate->getOrderDateState($country, (string) $order->getCreatedAt())
                : $this->estimate->getState($country, $showStore ? $this->getStoreTimezone($order) : null));
        } catch (\Throwable $exception) {
            // No exception context: SQL/errors can include personal data and order identifiers.
            $this->logger->warning('STONEHOLL_CUSTOMER_LOCAL_TIME_ESTIMATE_FAILED');
            return $base + ['status' => 'unavailable'];
        }
    }

    private function showCurrent(string $clock): bool
    {
        return $this->config->isSetFlag(
            'stoneholl_customer_local_time/general/show_' . $clock . '_time',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    private function getStoreTimezone(Order $order): ?string
    {
        $storeId = (int) $order->getStoreId();
        if ($storeId <= 0) {
            return null;
        }
        try {
            $store = $order->getStore();
            if (!$store || (int) $store->getId() !== $storeId) {
                return null;
            }
            return (string) $this->config->getValue(
                'general/locale/timezone',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Throwable $exception) {
            $this->logger->warning('STONEHOLL_CUSTOMER_LOCAL_TIME_STORE_TIMEZONE_UNAVAILABLE');
            return null;
        }
    }
}
