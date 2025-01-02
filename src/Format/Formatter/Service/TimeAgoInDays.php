<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter\Service;

use DateTimeInterface;
use Spiral\Core\Attribute\Singleton;
use Symfony\Component\Clock\DatePoint;

#[Singleton]
final class TimeAgoInDays
{

	public function format(DateTimeInterface $date, ?DateTimeInterface $reference = null): string
	{
		$reference ??= new DatePoint();

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
