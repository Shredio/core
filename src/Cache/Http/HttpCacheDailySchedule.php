<?php declare(strict_types = 1);

namespace Shredio\Core\Cache\Http;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class HttpCacheDailySchedule implements HttpCacheSchedule
{

	/**
	 * @param int<0, 23> $hours
	 * @param int<0, 59> $minutes
	 * @param array<int<0, 6>> $days 1-5 = Monday-Friday, 6 = Saturday, 0 = Sunday
	 */
	public function __construct(
		public readonly int $hours,
		public readonly int $minutes,
		public readonly array $days = [],
	)
	{
	}

	public function getExpiration(DateTimeImmutable $lastUpdate, ClockInterface $clock): ?DateTimeImmutable
	{
		$now = $clock->now();
		$lastSchedule = $now->setTime($this->hours, $this->minutes);
		$nextSchedule = $lastSchedule;

		if ($nextSchedule > $now) {
			$lastSchedule = $lastSchedule->modify('- 1 day');
		} else {
			$nextSchedule = $nextSchedule->modify('+ 1 day');
		}

		if ($this->days) {
			while (!in_array((int) $nextSchedule->format('w'), $this->days, true)) {
				$nextSchedule = $nextSchedule->modify('+ 1 day');
			}

			while (!in_array((int) $lastSchedule->format('w'), $this->days, true)) {
				$lastSchedule = $lastSchedule->modify('- 1 day');
			}
		}

		if ($lastSchedule > $lastUpdate) {
			return null;
		}

		return $nextSchedule;
	}

}
