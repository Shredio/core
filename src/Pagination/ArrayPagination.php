<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

use InvalidArgumentException;

final readonly class ArrayPagination implements ChainablePagination
{

	use PaginationMethods;

	public function __construct(
		private PaginationLinkGenerator $paginationLinkGenerator,
	)
	{
	}

	public function supports(iterable $results): bool
	{
		return is_array($results);
	}

	public function paginate(
		iterable $results,
		PaginationRequest $request,
		PaginationPointer $pointer,
	): PaginatedResults
	{
		if (!is_array($results)) {
			throw new InvalidArgumentException(sprintf('Expected array, got %s', get_debug_type($results)));
		}

		if (!$pointer instanceof PagePaginationPointer) {
			throw new InvalidArgumentException(sprintf('Expected %s, got %s', PagePaginationPointer::class, get_debug_type($pointer)));
		}

		$limit = $pointer->getLimit($request);
		$offset = $pointer->getOffset($request);

		$results = array_slice($results, $offset, $limit + 1); // Extra entity to check if there is a next page

		return $this->createResultsWithExtraResult($results, $request, $pointer, $this->paginationLinkGenerator);
	}

}
