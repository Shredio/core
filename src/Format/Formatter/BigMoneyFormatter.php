<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

use InvalidArgumentException;
use Shredio\Core\Format\Attribute\BigMoneyFormat;
use Shredio\Core\Format\Attribute\FormatAttribute;
use Shredio\Core\Format\Attribute\MoneyFormat;

final readonly class BigMoneyFormatter implements ValueFormatter
{

	private const Units = [
		'' => 1_000,
		'k' => 1_000_000,
		'M' => 1_000_000_000,
		'B' => 1_000_000_000_000,
		'T' => 1_000_000_000_000_000,
		'Q' => null,
	];

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

		[$prefix, $formatted] = $this->format($value);
		$suffix = $attribute->suffixSpace ? ' ' : '';

		return match ($currency) {
			'CZK' => $prefix . $formatted . $suffix . 'Kč',
			'EUR' => $prefix . '€' . $formatted,
			'USD' => $prefix . '$' . $formatted,
			'PLN' => $prefix . $formatted . $suffix . 'zł',
			'GBp' => $prefix . $formatted . $suffix . 'p',
			'GBP' => $prefix . '£' . $formatted,
			'JPY' => $prefix . '¥' . $formatted,
			default => $prefix . $formatted . $suffix . $currency,
		};
	}

	/**
	 * @return array{ string, string }
	 */
	private function format(float|int $value): array
	{
		$unit = '';
		$negative = $value < 0;
		$value = abs($value);
		$divideBy = 1;
		$previousLimit = 1;

		foreach (self::Units as $u => $limit) {
			if ($limit === null || $value < $limit) {
				$unit = $u;
				$divideBy = $previousLimit;

				break;
			}

			$previousLimit = $limit;
		}

		$value /= $divideBy;

		return [$negative < 0 ? '-' : '', FormatterHelper::format($value) . $unit];
	}

}
