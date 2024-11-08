<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Attribute;

use Attribute;
use Shredio\Core\Format\Formatter\TimeAgoInDaysFormatter;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class TimeAgoInDaysFormat extends FormatAttribute
{

	public static function createDefaultFormatter(): TimeAgoInDaysFormatter
	{
		return TimeAgoInDaysFormatter::createDefault();
	}

}
