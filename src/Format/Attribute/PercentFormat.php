<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Attribute;

use Attribute;
use Shredio\Core\Format\Formatter\PercentFormatter;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class PercentFormat extends FormatAttribute
{

	public function __construct(
		public int $decimals = 2,
		array $groups = [self::DefaultGroup],
	)
	{
		parent::__construct($groups);
	}

	public static function createDefaultFormatter(): PercentFormatter
	{
		return new PercentFormatter();
	}

}
