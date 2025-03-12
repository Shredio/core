<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

use InvalidArgumentException;

final readonly class PaginationChain implements Pagination
{

	/**
	 * @param iterable<ChainablePagination> $paginators
	 */
	public function __construct(
		private iterable $paginators,
	)
	{
	}

	public function paginate(
		iterable $results,
		PaginationRequest $request,
		PaginationPointer $pointer,
	): PaginatedResults
	{
		foreach ($this->paginators as $paginator) {
			if ($paginator->supports($results)) {
				return $paginator->paginate($results, $request, $pointer);
			}
		}

		throw new InvalidArgumentException(sprintf('No paginator found for %s', get_debug_type($results)));
	}

}
