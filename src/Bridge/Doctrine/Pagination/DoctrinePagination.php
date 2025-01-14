<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Pagination;

use InvalidArgumentException;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilder;
use Shredio\Core\Pagination\PagePaginationPointer;
use Shredio\Core\Pagination\PaginatedResults;
use Shredio\Core\Pagination\Pagination;
use Shredio\Core\Pagination\PaginationLinkGenerator;
use Shredio\Core\Pagination\PaginationPointer;
use Shredio\Core\Pagination\PaginationRequest;

final class DoctrinePagination implements Pagination
{

	public function __construct(
		private PaginationLinkGenerator $paginationLinkGenerator,
	)
	{
	}

	/**
	 * @template TKey
	 * @template TValue of object
	 * @param iterable<TKey, TValue> $results
	 * @return PaginatedResults<TKey, TValue>
	 */
	public function paginate(
		iterable $results,
		PaginationRequest $request,
		PaginationPointer $pointer,
	): PaginatedResults
	{
		if (!$results instanceof QueryBuilder) {
			throw new InvalidArgumentException(sprintf('Expected %s, got %s', QueryBuilder::class, get_debug_type($results)));
		}

		if (!$pointer instanceof PagePaginationPointer) {
			throw new InvalidArgumentException(sprintf('Expected %s, got %s', PagePaginationPointer::class, get_debug_type($pointer)));
		}

		$limit = $pointer->getLimit($request);

		$results->setMaxResults($limit + 1); // Extra entity to check if there is a next page
		$results->setFirstResult($pointer->getOffset($request));

		$entities = $results->getQuery()->getResult();

		$nextLink = null;
		$nextPointer = null;
		$prevLink = null;
		$prevPointer = null;
		$isLastPage = true;

		if (count($entities) > $limit) {
			$nextPage = $pointer->getNextPage($request);
			$nextLink = $this->paginationLinkGenerator->link($request, [$pointer->getParameterName() => $nextPage]);
			$nextPointer = (string) $nextPage;
			$isLastPage = false;

			array_pop($entities); // Remove the extra entity
		}

		if ($prevPage = $pointer->getPrevPage($request)) {
			$prevLink = $this->paginationLinkGenerator->link($request, [$pointer->getParameterName() => $prevPage]);
			$prevPointer = (string) $prevPage;
		}

		/** @var PaginatedResults<TKey, TValue> */
		return new PaginatedResults($entities, $prevLink, $prevPointer, $nextLink, $nextPointer, $isLastPage);
	}

}
