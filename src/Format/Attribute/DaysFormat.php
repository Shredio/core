<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Attribute;

use Attribute;
use Shredio\Core\Format\Formatter\DaysFormatter;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class DaysFormat extends FormatAttribute
{

	public static function createDefaultFormatter(): DaysFormatter
	{
		return new DaysFormatter();
	}

}
