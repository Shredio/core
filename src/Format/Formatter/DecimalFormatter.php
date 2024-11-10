<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

use Shredio\Core\Format\Attribute\DecimalFormat;
use Shredio\Core\Format\Attribute\FormatAttribute;
use Shredio\Core\Formatter\NumberFormatter;

final readonly class DecimalFormatter implements ValueFormatter
{

	public function getSupportedAttributes(): array
	{
		return [DecimalFormat::class];
	}

	public function formatValue(float|int|string $value, FormatAttribute $attribute, array $context = []): string
	{
		assert($attribute instanceof DecimalFormat);

		if (is_string($value)) {
			if (!is_numeric($value)) {
				return $value;
			}

			$value = (float) $value;
		}

		if ($attribute->decimals === null) {
			return NumberFormatter::decimal($value);
		} else {
			return number_format($value, $attribute->decimals);
		}
	}

}
