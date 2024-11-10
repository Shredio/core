<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

use Shredio\Core\Format\Attribute\DaysFormat;
use Shredio\Core\Format\Attribute\FormatAttribute;

final readonly class DaysFormatter implements ValueFormatter
{

	public function getSupportedAttributes(): array
	{
		return [DaysFormat::class];
	}

	public function formatValue(float|int|string $value, FormatAttribute $attribute, array $context = []): string
	{
		if (is_string($value)) {
			if (!is_numeric($value)) {
				return $value;
			}

			$value = (float) $value;
		}

		return number_format($value) . ' ' . ($value === 1 ? 'day' : 'days');
	}

}
