<?php declare(strict_types = 1);

namespace Shredio\Core\Format;

use InvalidArgumentException;
use Shredio\Core\Format\Attribute\FormatAttribute;
use Shredio\Core\Format\Formatter\ValueFormatter;

final class FormatterRegistry
{

	/**
	 * @param array<string, ValueFormatter> $formatters
	 */
	public function __construct(
		private array $formatters = [],
	)
	{
	}

	public function addFormatter(ValueFormatter $formatter): void
	{
		foreach ($formatter->getSupportedAttributes() as $attribute) {
			$this->formatters[$attribute] = $formatter;
		}
	}

	/**
	 * @param class-string<FormatAttribute> $attributeName
	 * @return ValueFormatter
	 */
	public function get(string $attributeName): ValueFormatter
	{
		return $this->formatters[$attributeName] ?? $this->tryCreateFormatter($attributeName);
	}

	/**
	 * @param class-string<FormatAttribute> $attributeName
	 */
	private function tryCreateFormatter(string $attributeName): ValueFormatter
	{
		if (method_exists($attributeName, 'createDefaultFormatter')) {
			$formatter = $attributeName::createDefaultFormatter();

			$this->addFormatter($formatter);

			return $formatter;
		}

		throw new InvalidArgumentException(sprintf('Formatter for "%s" not found.', $attributeName));
	}

}
