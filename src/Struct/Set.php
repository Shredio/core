<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use Countable;
use IteratorAggregate;

/**
 * @template T
 * @implements IteratorAggregate<int, T>
 */
abstract class Set implements IteratorAggregate, Countable
{

	use DataStructureExtensionDetection;

	/**
	 * @template TValue
	 * @param TValue[] $values
	 * @return self<TValue>
	 */
	public static function create(array $values = []): self
	{
		if (self::usePecl()) {
			return new PeclSet($values);
		}

		trigger_error(
			sprintf('Extension ds is not loaded, use type-safe method %s::createStringOrInt().', static::class),
			E_USER_WARNING,
		);

		return new ArraySet($values);
	}

	/**
	 * @template TValue of string|int
	 * @param TValue[] $values
	 * @return self<TValue>
	 */
	public static function createStringOrInt(array $values = []): self
	{
		if (self::usePecl()) {
			return new PeclSet($values);
		}

		return new ArraySet($values);
	}

	/**
	 * @param string[] $values
	 * @return self<string>
	 */
	public static function createString(array $values = []): self
	{
		if (self::usePecl()) {
			return new PeclSet($values);
		}

		return new ArraySet($values);
	}

	/**
	 * @param T $key
	 */
	abstract public function add(mixed $key): void;

	/**
	 * @param T $key
	 */
	abstract public function delete(mixed $key): void;

	/**
	 * @param T $key
	 */
	abstract public function has(mixed $key): bool;

	abstract public function clear(): void;

	/**
	 * @return T[]
	 */
	abstract public function toArray(): array;

}
