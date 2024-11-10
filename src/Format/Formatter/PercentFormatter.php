<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

use Shredio\Core\Format\Attribute\FormatAttribute;
use Shredio\Core\Format\Attribute\PercentFormat;

final readonly class PercentFormatter implements ValueFormatter
{

	public function getSupportedAttributes(): array
	{
		return [PercentFormat::class];
	}

	public function formatValue(float|int|string $value, FormatAttribute $attribute, array $context = []): string
	{
		assert($attribute instanceof PercentFormat);

		if (is_string($value)) {
			if (!is_numeric($value)) {
				return $value;
			}

			$value = (float) $value;
		}

		return number_format($value * 100, $attribute->decimals) . '%';
	}

}
