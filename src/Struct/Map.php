<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use OutOfBoundsException;

/**
 * @template TKey
 * @template TValue
 */
abstract class Map
{

	use DataStructureExtensionDetection;

	/**
	 * @return Map<string|int, mixed>
	 */
	public static function createStringOrInt(): Map
	{
		if (self::usePecl()) {
			return new PeclMap();
		}

		return new ArrayMap();
	}

	/**
	 * @template TV
	 * @param iterable<array{string|int, TV}> $values
	 * @return Map<string|int, TV>
	 */
	public static function createStringOrIntWith(iterable $values): Map
	{
		if (self::usePecl()) {
			return new PeclMap($values);
		}

		return new ArrayMap($values);
	}

	/**
	 * @return Map<string, mixed>
	 */
	public static function createString(): Map
	{
		if (self::usePecl()) {
			return new PeclMap();
		}

		/** @var ArrayMap<string, mixed> */
		return new ArrayMap();
	}

	/**
	 * @template TV
	 * @param iterable<array{string, TV}> $values
	 * @return Map<string, TV>
	 */
	public static function createStringWith(iterable $values): Map
	{
		if (self::usePecl()) {
			return new PeclMap($values);
		}

		/** @var ArrayMap<string, mixed> */
		return new ArrayMap($values);
	}

	abstract public function allocate(int $capacity): void;

	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	abstract public function set(mixed $key, mixed $value): void;

	/**
	 * @param TKey $key
	 */
	abstract public function has(mixed $key): bool;

	/**
	 * @throws OutOfBoundsException
	 * @param TKey $key
	 * @return TValue
	 */
	abstract public function get(mixed $key): mixed;

	/**
	 * @param TKey $key
	 * @param TValue $default
	 * @return TValue
	 */
	public function getOr(mixed $key, mixed $default): mixed
	{
		return $this->getValueOrNull($key) ?? $default;
	}

	/**
	 * @param TKey $key
	 * @return TValue|null
	 */
	abstract public function getValueOrNull(mixed $key): mixed;

	abstract public function isEmpty(): bool;

	abstract public function clear(): void;

}
