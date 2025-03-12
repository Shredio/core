<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

use InvalidArgumentException;

trait PaginationMethods
{

	/**
	 * @template TKey of array-key
	 * @template TValue
	 * @param array<TKey, TValue> $values
	 * @return PaginatedResults<TKey, TValue>
	 */
	private function createResultsWithExtraResult(
		array $values,
		PaginationRequest $request,
		PaginationPointer $pointer,
		PaginationLinkGenerator $paginationLinkGenerator,
	): PaginatedResults
	{
		if (!$pointer instanceof PagePaginationPointer) {
			throw new InvalidArgumentException(
				sprintf('Expected %s, got %s', PagePaginationPointer::class, get_debug_type($pointer)),
			);
		}

		$limit = $pointer->getLimit($request);
		$nextLink = null;
		$nextPointer = null;
		$prevLink = null;
		$prevPointer = null;
		$isLastPage = true;

		if (count($values) > $limit) {
			$nextPage = $pointer->getNextPage($request);
			$nextLink = $paginationLinkGenerator->link($request, [$pointer->getParameterName() => $nextPage]);
			$nextPointer = (string) $nextPage;
			$isLastPage = false;

			array_pop($values); // Remove the extra result
		}

		if ($prevPage = $pointer->getPrevPage($request)) {
			$prevLink = $paginationLinkGenerator->link($request, [$pointer->getParameterName() => $prevPage]);
			$prevPointer = (string) $prevPage;
		}

		/** @var PaginatedResults<TKey, TValue> */
		return new PaginatedResults($values, $prevLink, $prevPointer, $nextLink, $nextPointer, $isLastPage);
	}

}
