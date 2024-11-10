<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Attribute;

use Attribute;
use Shredio\Core\Format\Formatter\MoneyFormatter;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MoneyFormat extends FormatAttribute
{

	public const string CurrencyInContext = 'currency';

	public function __construct(
		public string|false|null $currency = false,
		array $groups = [self::DefaultGroup],
	)
	{
		parent::__construct($groups);
	}

	public static function createDefaultFormatter(): MoneyFormatter
	{
		return new MoneyFormatter();
	}

}
