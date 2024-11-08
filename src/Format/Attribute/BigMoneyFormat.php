<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Attribute;

use Attribute;
use Shredio\Core\Format\Formatter\BigMoneyFormatter;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class BigMoneyFormat extends FormatAttribute
{

	public function __construct(
		public string|false|null $currency = false,
		public bool $suffixSpace = true,
		array $groups = [self::DefaultGroup],
	)
	{
		parent::__construct($groups);
	}

	public static function createDefaultFormatter(): BigMoneyFormatter
	{
		return new BigMoneyFormatter();
	}

}
