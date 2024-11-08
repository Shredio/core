<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Customize;

use Shredio\Core\Format\ValuesFormatter;

interface CustomFormatting
{

	/**
	 * @param mixed[] $values
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	public static function format(array $values, ValuesFormatter $formatter, array $context): array;

}
