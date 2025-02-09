<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Result;

use Doctrine\ORM\Query;
use Shredio\Core\Common\Trait\DisallowSerialization;
use Shredio\Core\Struct\Set;

final readonly class DatabaseFieldResult
{

	use DisallowSerialization;

	public function __construct(
		private Query $query,
		private string $field,
	)
	{
	}

	/**
	 * @return mixed[]
	 */
	public function toArray(): array
	{
		return iterator_to_array($this->yieldArray());
	}

	/**
	 * @return mixed[]
	 */
	public function toScalarArray(): array
	{
		return iterator_to_array($this->yieldScalar());
	}

	/**
	 * @return Set<string>
	 */
	public function createStringSet(): Set
	{
		return Set::createString($this->yieldScalar());
	}

	/**
	 * @return mixed[]
	 */
	public function yieldArray(): iterable
	{
		foreach ($this->query->toIterable(hydrationMode: Query::HYDRATE_ARRAY) as $value) {
			yield $value[$this->field];
		}
	}

	/**
	 * @return mixed[]
	 */
	public function yieldScalar(): iterable
	{
		foreach ($this->query->toIterable(hydrationMode: Query::HYDRATE_SCALAR) as $value) {
			yield $value[$this->field];
		}
	}

}
