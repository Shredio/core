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
	 * @return TValue|null
	 */
	abstract public function getValueOrNull(mixed $key): mixed;
	
}
