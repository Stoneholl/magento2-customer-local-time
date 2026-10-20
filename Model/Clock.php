<?php

declare(strict_types=1);

namespace Stoneholl\CustomerLocalTime\Model;

class Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
