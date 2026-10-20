<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Model;

/** A single-zone address estimate is never a verified customer location. */
class Estimate
{
    /** @var CountryZones */
    private $zones;
    /** @var Clock */
    private $clock;

    public function __construct(CountryZones $zones, Clock $clock)
    {
        $this->zones = $zones;
        $this->clock = $clock;
    }

    public function getState(?string $country, ?string $storeTimezone = null): array
    {
        $instant = $this->clock->now()->setTimezone(new \DateTimeZone('UTC'));
        $state = [
            'as_of' => $instant->format('Y-m-d H:i') . ' UTC',
            'store_time' => $this->getStoreTime($instant, $storeTimezone)
        ];
        if ($country === null) {
            return $state + ['status' => 'address_missing'];
        }
        $zones = $this->zones->getZones($country);
        if ($zones === null) {
            return $state + ['status' => 'country_unknown'];
        }
        if (count($zones) === 0) {
            return $state + ['status' => 'timezone_unknown'];
        }
        if (count($zones) !== 1) {
            return $state + ['status' => 'multiple_timezones'];
        }
        $local = $instant->setTimezone(new \DateTimeZone($zones[0]));
        return $state + [
            'status' => 'ready',
            'local_time' => $local->format('Y-m-d H:i'),
            'time' => $local->format('h:i A'),
            'timezone' => $zones[0],
            'offset' => 'UTC' . $local->format('P')
        ];
    }

    public function getOrderDateState(?string $country, string $createdAt): array
    {
        $instant = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $createdAt, new \DateTimeZone('UTC'));
        if (!$instant || $instant->format('Y-m-d H:i:s') !== $createdAt) {
            return ['status' => 'unavailable'];
        }
        if ($country === null) {
            return ['status' => 'address_missing'];
        }
        $zones = $this->zones->getZones($country);
        if ($zones === null) {
            return ['status' => 'country_unknown'];
        }
        if (count($zones) !== 1) {
            return ['status' => count($zones) === 0 ? 'timezone_unknown' : 'multiple_timezones'];
        }
        return [
            'status' => 'ready',
            'order_date' => $instant->setTimezone(new \DateTimeZone($zones[0]))->format('M j, Y, g:i:s A')
        ];
    }

    private function getStoreTime(\DateTimeImmutable $instant, ?string $storeTimezone): ?string
    {
        if ($storeTimezone === null || $storeTimezone === '') {
            return null;
        }
        try {
            return $instant->setTimezone(new \DateTimeZone($storeTimezone))->format('h:i A');
        } catch (\Exception $exception) {
            return null;
        }
    }
}
