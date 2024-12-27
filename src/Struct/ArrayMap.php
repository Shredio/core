<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use OutOfBoundsException;

/**
 * @template TKey of string|int
 * @template TValue
 * @extends Map<TKey, TValue>
 */
final class ArrayMap extends Map
{

	/** @var array<TKey, TValue> */
	private array $map = [];

	/**
	 * @param iterable<array{TKey, TValue}> $values
	 */
	public function __construct(iterable $values = [])
	{
		foreach ($values as $value) {
			$this->map[$value[0]] = $value[1];
		}
	}

	public function allocate(int $capacity): void
	{
	}

	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function set(mixed $key, mixed $value): void
	{
		$this->map[$key] = $value;
	}

	/**
	 * @param TKey $key
	 */
	public function has(mixed $key): bool
	{
		return isset($this->map[$key]);
	}

	/**
	 * @param TKey $key
	 * @return TValue
	 */
	public function get(mixed $key): mixed
	{
		if (!array_key_exists($key, $this->map)) {
			throw new OutOfBoundsException(sprintf('Key %s not found', $key));
		}

		return $this->map[$key];
	}

	/**
	 * @param TKey $key
	 * @return TValue|null
	 */
	public function getValueOrNull(mixed $key): mixed
	{
		return $this->map[$key] ?? null;
	}

	public function isEmpty(): bool
	{
		return !$this->map;
	}

	public function clear(): void
	{
		$this->map = [];
	}

}
