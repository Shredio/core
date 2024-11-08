<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Formatter;

use Shredio\Core\Format\Attribute\FormatAttribute;

interface ValueFormatter
{

	/**
	 * @return class-string[]
	 */
	public function getSupportedAttributes(): array;

	/**
	 * @param mixed[] $context
	 */
	public function formatValue(int|float|string $value, FormatAttribute $attribute, array $context = []): string;

}
