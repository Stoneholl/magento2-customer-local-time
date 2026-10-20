<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Model;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

/** Country metadata and timezone lists are cached only for this request. */
class CountryZones
{
    /** @var CollectionFactory */
    private $countries;
    /** @var array|null */
    private $countryIds;
    /** @var array */
    private $zones = [];

    public function __construct(CollectionFactory $countries)
    {
        $this->countries = $countries;
    }

    /**
     * Return null for an invalid country, otherwise all installed IANA identifiers.
     *
     * @param string $country
     * @return array|null
     */
    public function getZones(string $country): ?array
    {
        if (!preg_match('/^[A-Z]{2}$/D', $country)) {
            return null;
        }
        if ($this->countryIds === null) {
            $this->countryIds = $this->countries->create()->getAllIds();
        }
        if (!in_array($country, $this->countryIds, true)) {
            return null;
        }
        if (!array_key_exists($country, $this->zones)) {
            $this->zones[$country] = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country);
        }
        return $this->zones[$country];
    }
}
