<?php declare(strict_types = 1);

namespace Shredio\Core\Formatter;

final class NumberFormatter
{

	/** @var array<string, int|null> */
	private const array BigMoneyUnits = [
		'' => 1_000,
		'k' => 1_000_000,
		'M' => 1_000_000_000,
		'B' => 1_000_000_000_000,
		'T' => 1_000_000_000_000_000,
		'Q' => null,
	];

	public static function percentageChange(?float $value, string $otherwise = '-'): string
	{
		return self::nullablePercentageChange($value) ?? $otherwise;
	}

	public static function nullablePercentageChange(?float $value): ?string
	{
		if ($value === null) {
			return null;
		}

		$formatted = number_format($value * 100, 2);

		$prefix = '';

		if ($value > 0) {
			$prefix = '+';
		}

		return $formatted === '0.00' ? '0%' : $prefix . $formatted . '%';
	}

	public static function decimal(?float $price, string $otherwise = '-'): string
	{
		return self::nullableDecimal($price) ?? $otherwise;
	}

	public static function nullableDecimal(?float $value): ?string
	{
		if ($value === null) {
			return null;
		}

		$abs = abs($value);

		return match (true) {
			$abs >= 1000 => number_format($value),
			$abs >= 100 => number_format($value, 1),
			$abs >= 0.01 => number_format($value, 2),
			$abs >= 0.0001 => number_format($value, 4),
			default => self::formatVerySmallValue($value),
		};
	}

	public static function bigMoney(?float $value, ?string $currency = null, string $otherwise = '-'): string
	{
		return self::nullableBigMoney($value, $currency) ?? $otherwise;
	}

	public static function nullableBigMoney(?float $value, ?string $currency = null): ?string
	{
		if ($value === null) {
			return null;
		}

		$unit = '';
		$prefix = $value < 0 ? '-' : '';
		$value = abs($value);
		$divideBy = 1;
		$previousLimit = 1;

		foreach (self::BigMoneyUnits as $u => $limit) {
			if ($limit === null || $value < $limit) {
				$unit = $u;
				$divideBy = $previousLimit;

				break;
			}

			$previousLimit = $limit;
		}

		$value /= $divideBy;

		if ($value > 5) {
			$value = number_format($value);
		} else {
			$value = number_format($value, 1);

			if (str_ends_with($value, '.0')) {
				$value = substr($value, 0, -2);
			}
		}

		return self::formatMoney($prefix . $value . $unit, $currency);
	}

	public static function shortMoney(?float $value, ?string $currency, string $otherwise = '-'): string
	{
		return self::nullableShortMoney($value, $currency) ?? $otherwise;
	}

	public static function nullableShortMoney(?float $value, ?string $currency): ?string
	{
		if ($value === null) {
			return null;
		}

		$abs = abs($value);

		if ($abs > 999) {
			return self::nullableBigMoney($value, $currency);
		}

		$decimals = $abs < 10 ? 2 : 1;

		$formatted = number_format($value, $decimals);

		if ($decimals === 2 && str_ends_with($formatted, '.00')) {
			$formatted = substr($formatted, 0, -3);
		} else if ($decimals === 1 && str_ends_with($formatted, '.0')) {
			$formatted = substr($formatted, 0, -2);
		}

		return self::formatMoney($formatted, $currency);
	}

	public static function money(?float $value, ?string $currency = null, string $otherwise = '-'): string
	{
		return self::nullableMoney($value, $currency) ?? $otherwise;
	}

	public static function nullableMoney(?float $value, ?string $currency = null): ?string
	{
		if ($value === null) {
			return null;
		}

		return self::formatMoney(self::decimal($value), $currency);
	}

	private static function formatMoney(string $formatted, ?string $currency): string
	{
		if ($currency === null) {
			return $formatted;
		}

		$config = CurrencyDatabase::getCurrencyConfiguration($currency);

		if (!$config) {
			return $formatted . ' ' . $currency;
		}

		if (!$config['isBefore']) {
			return $formatted . ' ' . $config['symbol'];
		}

		$prefix = $config['symbol'];

		if (str_starts_with($formatted, '-')) {
			$prefix = '-' . $prefix;
			$formatted = substr($formatted, 1);
		}

		return $prefix . $formatted;
	}

	private static function formatVerySmallValue(float $value): string
	{
		$formatted = number_format($value, 6);

		return $formatted === '0.000000' ? '0.00' : $formatted;
	}

}
