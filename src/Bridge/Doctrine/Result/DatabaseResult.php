<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Result;

use Doctrine\ORM\Query;
use LogicException;
use Shredio\Core\Common\Trait\DisallowSerialization;

/**
 * @template T of object
 */
final readonly class DatabaseResult
{

	use DisallowSerialization;

	/**
	 * @param class-string<T> $className
	 */
	public function __construct(
		private Query $query,
		private string $className,
		private bool $partial = false,
	)
	{
	}

	/**
	 * @return mixed[]
	 */
	public function toArray(): array
	{
		return $this->query->getArrayResult();
	}

	/**
	 * @return mixed[]
	 */
	public function toScalar(): array
	{
		return $this->query->getScalarResult();
	}

	/**
	 * @return iterable<mixed[]>
	 */
	public function yieldArray(): iterable
	{
		return $this->query->toIterable(hydrationMode: Query::HYDRATE_ARRAY);
	}

	/**
	 * @return T[]
	 */
	public function toObjects(): array
	{
		if ($this->partial) {
			throw new LogicException('Partial object hydration is not supported');
		}

		return $this->query->getResult();
	}

	/**
	 * @return iterable<T>
	 */
	public function yieldObjects(): iterable
	{
		if ($this->partial) {
			throw new LogicException('Partial object hydration is not supported');
		}

		return $this->query->toIterable();
	}

	/**
	 * @return class-string<T>
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

}
