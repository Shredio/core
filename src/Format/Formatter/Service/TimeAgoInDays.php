<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter\Service;

use DateTimeImmutable;
use DateTimeInterface;
use Spiral\Core\Attribute\Singleton;

#[Singleton]
final class TimeAgoInDays
{

	public function format(DateTimeInterface $date, ?DateTimeInterface $reference = null): string
	{
		$reference ??= new DateTimeImmutable();

		$interval = $reference->diff($date);
		$days = (int) $interval->format('%r%a');

		if ($days > 0) {
			return sprintf('in %d days', $days);
		} elseif ($days < 0) {
			return abs($days) . ' days ago';
		} else {
			return 'today';
		}
	}

}
