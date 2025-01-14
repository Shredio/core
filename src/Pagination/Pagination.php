<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

interface Pagination
{

	/**
	 * @template TKey
	 * @template TValue of object
	 * @param iterable<TKey, TValue> $results
	 * @return PaginatedResults<TKey, TValue>
	 */
	public function paginate(iterable $results, PaginationRequest $request, PaginationPointer $pointer): PaginatedResults;

}
