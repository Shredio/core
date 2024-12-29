<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

interface PaginationFactory
{

	/**
	 * @template TKey
	 * @template TValue of object
	 * @param iterable<TKey, TValue> $values
	 * @return iterable<TKey, TValue>
	 */
	public function create(iterable $values, int $limit, ?int $page): iterable;

}
