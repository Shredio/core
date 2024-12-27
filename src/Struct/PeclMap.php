<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use Ds\Map as DsMap;
use OutOfBoundsException;

/**
 * @template TKey
 * @template TValue
 * @extends Map<TKey, TValue>
 */
final class PeclMap extends Map
{

	/** @var DsMap<TKey, TValue> */
	private DsMap $map;

	/**
	 * @param iterable<array{TKey, TValue}> $values
	 */
	public function __construct(iterable $values = [])
	{
		$this->map = new DsMap();
		
		if (is_array($values)) {
			$this->map->allocate(count($values));
		}
		
		foreach ($values as $value) {
			$this->map->put($value[0], $value[1]);
		}
	}

	public function allocate(int $capacity): void
	{
		$this->map->allocate($capacity);
	}

	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function set(mixed $key, mixed $value): void
	{
		$this->map->put($key, $value);
	}

	/**
	 * @param TKey $key
	 */
	public function has(mixed $key): bool
	{
		return $this->map->hasKey($key);
	}

	/**
	 * @param TKey $key
	 * @return TValue|null
	 */
	public function get(mixed $key): mixed
	{
		return $this->map->get($key);
	}

	/**
	 * @param TKey $key
	 * @return TValue|null
	 */
	public function getValueOrNull(mixed $key): mixed
	{
		return $this->map->get($key, null);
	}

	public function isEmpty(): bool
	{
		return $this->map->isEmpty();
	}

	public function clear(): void
	{
		$this->map->clear();
	}

}
