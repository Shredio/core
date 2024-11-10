<?php declare(strict_types = 1);

namespace Tests\Formatter;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shredio\Core\Formatter\NumberFormatter;

final class NumberFormatterTest extends TestCase
{

	#[TestWith([10000, '10,000'])]
	#[TestWith([1000, '1,000'])]
	#[TestWith([100, '100.0'])]
	#[TestWith([10, '10.00'])]
	#[TestWith([1, '1.00'])]
	#[TestWith([0, '0.00'])]
	#[TestWith([0.1, '0.10'])]
	#[TestWith([0.01, '0.01'])]
	#[TestWith([0.001, '0.0010'])]
	#[TestWith([0.0001, '0.0001'])]
	#[TestWith([0.00001, '0.000010'])]
	#[TestWith([0.000001, '0.000001'])]
	#[TestWith([0.0000001, '0.00'])]
	public function testDecimals(float $number, string $expected): void
	{
		$this->assertSame($expected, NumberFormatter::decimal($number));
	}

	public function testNullDecimals(): void
	{
		$this->assertSame('-', NumberFormatter::decimal(null));
		$this->assertNull(NumberFormatter::nullableDecimal(null));
	}

	#[TestWith([100, 'CZK', '100.0 Kč'])]
	#[TestWith([-100, 'CZK', '-100.0 Kč'])]
	#[TestWith([100, 'USD', '$100.0'])]
	#[TestWith([-100, 'USD', '-$100.0'])]
	#[TestWith([100, 'GBP', '£100.0'])]
	#[TestWith([100, 'PLN', '100.0 zł'])]
	#[TestWith([100, 'EUR', '€100.0'])]
	#[TestWith([100, 'GBX', '100.0 GBX'])]
	public function testMoney(float $number, ?string $currency, string $expected): void
	{
		$this->assertSame($expected, NumberFormatter::money($number, $currency));
	}

	public function testNullMoney(): void
	{
		$this->assertSame('-', NumberFormatter::money(null));
		$this->assertNull(NumberFormatter::nullableMoney(null));
	}

	#[TestWith([1.00, '+100.00%'])]
	#[TestWith([0.01, '+1.00%'])]
	#[TestWith([0.001, '+0.10%'])]
	#[TestWith([0, '0%'])]
	#[TestWith([-0.001, '-0.10%'])]
	public function testPercentageChanges(float $number, string $expected): void
	{
		$this->assertSame($expected, NumberFormatter::percentageChange($number));
	}

	public function testNullPercentageChanges(): void
	{
		$this->assertSame('-', NumberFormatter::percentageChange(null));
		$this->assertNull(NumberFormatter::nullablePercentageChange(null));
	}

	#[TestWith([-10000, '-10k'])]
	#[TestWith([-4100, '-4.1k'])]
	#[TestWith([100, '100'])]
	#[TestWith([1000, '1k'])]
	#[TestWith([4100, '4.1k'])]
	#[TestWith([10000, '10k'])]
	#[TestWith([100000, '100k'])]
	#[TestWith([1000000, '1M'])]
	#[TestWith([10000000, '10M'])]
	#[TestWith([100000000, '100M'])]
	#[TestWith([1000000000, '1B'])]
	#[TestWith([10000000000, '10B'])]
	#[TestWith([100000000000, '100B'])]
	#[TestWith([1000000000000, '1T'])]
	#[TestWith([10000000000000, '10T'])]
	#[TestWith([100000000000000, '100T'])]
	#[TestWith([1000000000000000, '1Q'])]
	#[TestWith([10000000000000000, '10Q'])]
	public function testBigMoney(float $value, string $expected): void
	{
		$this->assertSame($expected, NumberFormatter::bigMoney($value));
	}

	#[TestWith([-10000, 'USD', '-$10k'])]
	#[TestWith([-4100, 'USD', '-$4.1k'])]
	#[TestWith([100, 'USD', '$100'])]
	#[TestWith([1000, 'USD', '$1k'])]
	#[TestWith([4100, 'USD', '$4.1k'])]
	#[TestWith([10000, 'USD', '$10k'])]
	#[TestWith([-10000, 'CZK', '-10k Kč'])]
	#[TestWith([-4100, 'CZK', '-4.1k Kč'])]
	#[TestWith([100, 'CZK', '100 Kč'])]
	#[TestWith([1000, 'CZK', '1k Kč'])]
	#[TestWith([4100, 'CZK', '4.1k Kč'])]
	#[TestWith([10000, 'CZK', '10k Kč'])]
	#[TestWith([10000, 'GBp', '10k GBX'])]
	public function testBigMoneyWithCurrency(float $value, string $currency, string $expected): void
	{
		$this->assertSame($expected, NumberFormatter::bigMoney($value, $currency));
	}

	public function testNullBigMoney(): void
	{
		$this->assertSame('-', NumberFormatter::bigMoney(null));
		$this->assertNull(NumberFormatter::nullableBigMoney(null));
	}

	#[TestWith([1000, 'USD', '$1k'])]
	#[TestWith([1000, 'CZK', '1k Kč'])]
	#[TestWith([100, 'USD', '$100'])]
	#[TestWith([100.12, 'USD', '$100.1'])]
	#[TestWith([10, 'USD', '$10'])]
	#[TestWith([10.12, 'USD', '$10.1'])]
	#[TestWith([5.12, 'USD', '$5.12'])]
	#[TestWith([5, 'USD', '$5'])]
	#[TestWith([0, 'USD', '$0'])]
	#[TestWith([-10, 'USD', '-$10'])]
	public function testShortMoney(float $value, string $currency, string $expected): void
	{
		$this->assertSame($expected, NumberFormatter::shortMoney($value, $currency));
	}

}
