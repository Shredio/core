<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

final class FormatterHelper
{

	public static function format(float|int $value, int $decimals = 2, bool $flexibleDecimals = true): string
	{
		if ($flexibleDecimals && is_float($value)) {
			$minimal = 0.1 ** $decimals;

			if ($minimal > abs($value)) {
				$decimals += 1;
			}
		}

		return rtrim(rtrim(number_format($value, $decimals), '0'), '.');
	}

}
