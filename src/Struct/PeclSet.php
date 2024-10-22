<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use Exception;
use Traversable;

/**
 * @template T
 * @extends Set<T>
 */
final class PeclSet extends Set
{

	/** @var \Ds\Set<T> */
	private \Ds\Set $set;

	/**
	 * @param T[] $values
	 */
	public function __construct(array $values)
	{
		$this->set = new \Ds\Set($values);
	}

	/**
	 * @param T $key
	 */
	public function add(mixed $key): void
	{
		$this->set->add($key);
	}

	/**
	 * @param T $key
	 */
	public function delete(mixed $key): void
	{
		$this->set->remove($key);
	}

	/**
	 * @param T $key
	 */
	public function has(mixed $key): bool
	{
		return $this->set->contains($key);
	}

	public function clear(): void
	{
		$this->set->clear();
	}

	/**
	 * @return T[]
	 */
	public function toArray(): array
	{
		return $this->set->toArray();
	}

	public function getIterator(): Traversable
	{
		return $this->set->getIterator();
	}

	public function count(): int
	{
		return $this->set->count();
	}

}
