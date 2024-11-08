<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

use DateTimeImmutable;
use Shredio\Core\Format\Attribute\FormatAttribute;
use Shredio\Core\Format\Attribute\TimeAgoInDaysFormat;
use Shredio\Core\Format\Formatter\Service\TimeAgoInDays;
use Throwable;

final readonly class TimeAgoInDaysFormatter implements ValueFormatter
{

	public function __construct(
		private TimeAgoInDays $timeAgoInDays,
	)
	{
	}

	public static function createDefault(): self
	{
		return new self(new TimeAgoInDays());
	}

	public function getSupportedAttributes(): array
	{
		return [TimeAgoInDaysFormat::class];
	}

	public function formatValue(float|int|string $value, FormatAttribute $attribute, array $context = []): string
	{
		if (is_string($value)) {
			try {
				$date = new DateTimeImmutable($value);
			} catch (Throwable) {
				return $value;
			}

			return $this->timeAgoInDays->format($date);
		}

		return (string) $value;
	}

}
