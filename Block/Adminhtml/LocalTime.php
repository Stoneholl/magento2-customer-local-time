<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;
use Magento\Sales\Model\Order;
use Stoneholl\CustomerLocalTime\Model\LocalTime as LocalTimeModel;

class LocalTime extends Template
{
    /** @var LocalTimeModel */
    private $localTime;

    public function __construct(Context $context, LocalTimeModel $localTime, array $data = [])
    {
        $this->localTime = $localTime;
        parent::__construct($context, $data);
    }

    public function getLocalTimeState(): array
    {
        $feature = $this->getData('feature') === 'order_date' ? 'order_date' : 'time_information';
        if (!$this->localTime->isAllowed($feature)) {
            return ['status' => 'hidden'];
        }
        $info = $this->getLayout()->getBlock('order_tab_info');
        if (!$info instanceof Info) {
            return ['status' => 'hidden'];
        }
        try {
            $order = $info->getOrder();
            return $order instanceof Order ? $this->localTime->getState($order, $feature) : ['status' => 'hidden'];
        } catch (\Throwable $exception) {
            $this->_logger->warning('STONEHOLL_CUSTOMER_LOCAL_TIME_CURRENT_ORDER_UNAVAILABLE');
            return ['status' => 'hidden'];
        }
    }

    public function getOutputEscaper(): \Magento\Framework\Escaper
    {
        return $this->_escaper;
    }
}
