<?php declare(strict_types = 1);

namespace Shredio\Core\Cache\Http;

use DateTimeImmutable;
use Symfony\Component\Clock\ClockInterface;

interface HttpCacheSchedule
{

	public function getExpiration(DateTimeImmutable $lastUpdate, ClockInterface $clock): ?DateTimeImmutable;

}
