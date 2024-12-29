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
		$values = [];

		foreach ($this->yield() as $item) {
			$values[] = $item[$this->field];
		}

		return $values;
	}

	/**
	 * @return Set<string>
	 */
	public function createStringSet(): Set
	{
		$set = Set::createString();

		foreach ($this->yield() as $item) {
			$set->add($item[$this->field]);
		}

		return $set;
	}

	/**
	 * @return mixed[]
	 */
	public function yield(): iterable
	{
		return $this->query->toIterable(hydrationMode: Query::HYDRATE_ARRAY);
	}

}
