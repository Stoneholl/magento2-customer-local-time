<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Plugin\Adminhtml;

use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Stoneholl\CustomerLocalTime\Block\Adminhtml\LocalTime;

/** Native Info has no layout hook immediately after Order Date; leave custom templates untouched. */
class OrderDate
{
    public function afterToHtml(Info $subject, string $html): string
    {
        if (strpos($html, 'stoneholl-client-order-date') !== false) {
            return $html;
        }
        $pattern = '~<table\b[^>]*class="[^"]*\border-information-table\b[^"]*"[^>]*>\s*'
            . '(?:<tbody\b[^>]*>\s*)?<tr\b[^>]*>.*?</tr>~si';
        if (preg_match($pattern, $html, $match, PREG_OFFSET_CAPTURE) !== 1) {
            return $html;
        }
        $block = $subject->getChildBlock('stoneholl_client_order_date');
        if (!$block instanceof LocalTime) {
            return $html;
        }
        $row = $block->toHtml();
        $position = $match[0][1] + strlen($match[0][0]);
        return substr($html, 0, $position) . $row . substr($html, $position);
    }
}
