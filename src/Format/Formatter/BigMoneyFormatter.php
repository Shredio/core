<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

use InvalidArgumentException;
use Shredio\Core\Format\Attribute\BigMoneyFormat;
use Shredio\Core\Format\Attribute\FormatAttribute;
use Shredio\Core\Format\Attribute\MoneyFormat;
use Shredio\Core\Formatter\NumberFormatter;

final readonly class BigMoneyFormatter implements ValueFormatter
{

	public function getSupportedAttributes(): array
	{
		return [BigMoneyFormat::class];
	}

	public function formatValue(float|int|string $value, FormatAttribute $attribute, array $context = []): string
	{
		assert($attribute instanceof BigMoneyFormat);

		if ($attribute->currency === false) {
			$currency = $context[MoneyFormat::CurrencyInContext] ?? throw new InvalidArgumentException(
				'Currency must be provided in context when attribute does not specify it.'
			);
		} else {
			$currency = $attribute->currency;
		}

		if (is_string($value)) {
			if (!is_numeric($value)) {
				return $value;
			}

			$value = (float) $value;
		}

		return NumberFormatter::bigMoney($value, $currency);
	}

}
