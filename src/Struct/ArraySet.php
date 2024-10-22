<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use Traversable;

/**
 * @template T of array-key
 * @extends Set<T>
 */
final class ArraySet extends Set
{

	/** @var array<T, bool> */
	private array $values = [];

	/**
	 * @param array<T> $values
	 */
	public function __construct(array $values)
	{
		foreach ($values as $value) {
			$this->add($value);
		}
	}

	/**
	 * @param T $key
	 */
	public function add(mixed $key): void
	{
		$this->values[$key] = true;
	}

	/**
	 * @param T $key
	 */
	public function delete(mixed $key): void
	{
		unset($this->values[$key]);
	}

	/**
	 * @param T $key
	 */
	public function has(mixed $key): bool
	{
		return isset($this->values[$key]);
	}

	public function clear(): void
	{
		$this->values = [];
	}

	public function toArray(): array
	{
		return array_keys($this->values);
	}

	public function getIterator(): Traversable
	{
		foreach ($this->values as $key => $_) {
			yield $key;
		}
	}

	public function count(): int
	{
		return count($this->values);
	}

}
