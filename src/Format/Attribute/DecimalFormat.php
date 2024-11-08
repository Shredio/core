<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Attribute;

use Attribute;
use Shredio\Core\Format\Formatter\DecimalFormatter;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class DecimalFormat extends FormatAttribute
{

	public function __construct(
		public int $decimals = 2,
		public bool $flexibleDecimals = true,
		array $groups = [self::DefaultGroup],
	)
	{
		parent::__construct($groups);
	}

	public static function createDefaultFormatter(): DecimalFormatter
	{
		return new DecimalFormatter();
	}

}
