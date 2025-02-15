<?php declare(strict_types = 1);

namespace Shredio\Core\Common\Reflection;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

final class ReflectionHelper
{

	/**
	 * @template T of object
	 * @param ReflectionProperty|ReflectionParameter|ReflectionMethod|ReflectionClass<object> $reflection
	 * @param class-string<T> $className
	 * @return T|null
	 */
	public static function getAttribute(
		ReflectionProperty|ReflectionParameter|ReflectionMethod|ReflectionClass $reflection,
		string $className,
		bool $instanceOf = false,
	): ?object
	{
		$attribute = $reflection->getAttributes($className, $instanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0)[0] ?? null;

		/** @var T|null */
		return $attribute?->newInstance();
	}

	/**
	 * @param ReflectionProperty|ReflectionParameter|ReflectionMethod|ReflectionClass<object> $reflection
	 * @param class-string $className
	 */
	public static function hasAttribute(
		ReflectionProperty|ReflectionParameter|ReflectionMethod|ReflectionClass $reflection,
		string $className,
		bool $instanceOf = false,
	): bool
	{
		return $reflection->getAttributes($className, $instanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0) !== [];
	}

	/**
	 * @template T of object
	 * @param ReflectionProperty|ReflectionParameter|ReflectionMethod|ReflectionClass<object> $reflection
	 * @param class-string<T> $className
	 * @return iterable<T>
	 */
	public static function getAttributes(
		ReflectionProperty|ReflectionParameter|ReflectionMethod|ReflectionClass $reflection,
		string $className,
		bool $instanceOf = false,
	): iterable
	{
		foreach ($reflection->getAttributes($className, $instanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0) as $attribute) {
			yield $attribute->newInstance();
		}
	}

	public static function getClassName(string $fullClassName): string
	{
		$pos = strrpos($fullClassName, '\\');

		if ($pos === false) {
			return $fullClassName;
		}

		return substr($fullClassName, $pos + 1);
	}

	public static function setProperty(object $object, string $property, mixed $value): void
	{
		$reflection = new ReflectionProperty($object, $property);
		$reflection->setValue($object, $value);
	}

}
