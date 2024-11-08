<?php declare(strict_types = 1);

namespace Shredio\Core\Format;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Shredio\Core\Format\Attribute\FormatAttribute;
use Shredio\Core\Format\Customize\CustomFormatting;
use Spiral\Core\Attribute\Singleton;

#[Singleton]
final class ValuesFormatter
{

	public const CurrencyContext = 'currency';
	public const GroupsContext = 'groups';

	public function __construct(
		private readonly FormatterRegistry $formatterRegistry,
	)
	{
	}

	/**
	 * @param iterable<FormatAttribute> $attributes
	 * @param mixed[] $context
	 */
	public function formatField(mixed $value, iterable $attributes, array $context = []): mixed
	{
		if (!is_scalar($value) || is_bool($value)) {
			return $value;
		}

		$groups = $context[self::GroupsContext] ?? ['default'];

		foreach ($attributes as $attribute) {
			if (!in_array('*', $attribute->groups, true) && !$this->hasIntersection($attribute->groups, $groups)) {
				continue;
			}

			$formatter = $this->formatterRegistry->get($attribute::class);
			$value = $formatter->formatValue($value, $attribute, $context);
		}

		return $value;
	}

	/**
	 * @param mixed[] $values
	 * @param class-string $className
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	public function format(array $values, string $className, array $context = []): array
	{
		$reflection = new ReflectionClass($className);
		$properties = $this->getPropertyMap($reflection);

		foreach ($values as $key => &$value) {
			if (!is_scalar($value) || is_bool($value)) {
				continue;
			}

			$property = $properties[$key] ?? null;

			if (!$property) {
				continue;
			}

			$value = $this->formatField($value, $this->getAttributes($property), $context);
		}

		if (is_a($className, CustomFormatting::class, true)) {
			$values = $className::format($values, $this, $context);
		}

		return $values;
	}

	/**
	 * @return iterable<FormatAttribute>
	 */
	private function getAttributes(ReflectionProperty $property): iterable
	{
		foreach ($property->getAttributes(FormatAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
			yield $attribute->newInstance();
		}
	}

	/**
	 * @param ReflectionClass<object> $reflection
	 * @return array<string, ReflectionProperty>
	 */
	private function getPropertyMap(ReflectionClass $reflection): array
	{
		$map = [];

		foreach ($reflection->getProperties() as $property) {
			$map[$property->getName()] = $property;
		}

		return $map;
	}

	/**
	 * @param mixed[] $first
	 * @param mixed[] $second
	 */
	private function hasIntersection(array $first, array $second): bool
	{
		foreach ($first as $item) {
			if (in_array($item, $second, true)) {
				return true;
			}
		}

		return false;
	}

}
