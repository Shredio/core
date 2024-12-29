<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Query;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use IteratorAggregate;
use LogicException;
use Traversable;

/**
 * @template T of object
 * @implements IteratorAggregate<int, T>
 */
class QueryBuilder extends DoctrineQueryBuilder implements IteratorAggregate
{

	/**
	 * @return Traversable<int, T>
	 */
	public function getIterator(): Traversable
	{
		$iterable = $this->getQuery()->toIterable();

		if (!$iterable instanceof Traversable) {
			throw new LogicException('Query is not iterable');
		}

		return $iterable;
	}

}
