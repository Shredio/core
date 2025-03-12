<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Pagination;

use InvalidArgumentException;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilder;
use Shredio\Core\Pagination\ChainablePagination;
use Shredio\Core\Pagination\PagePaginationPointer;
use Shredio\Core\Pagination\PaginatedResults;
use Shredio\Core\Pagination\PaginationLinkGenerator;
use Shredio\Core\Pagination\PaginationMethods;
use Shredio\Core\Pagination\PaginationPointer;
use Shredio\Core\Pagination\PaginationRequest;

final readonly class DoctrinePagination implements ChainablePagination
{

	use PaginationMethods;

	public function __construct(
		private PaginationLinkGenerator $paginationLinkGenerator,
	)
	{
	}

	public function supports(iterable $results): bool
	{
		return $results instanceof QueryBuilder;
	}

	/**
	 * @template TKey of array-key
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
		$offset = $pointer->getOffset($request);

		$results->setMaxResults($limit + 1); // Extra entity to check if there is a next page
		$results->setFirstResult($offset);

		/** @var array<TKey, TValue> $entities */
		$entities = $results->getQuery()->getResult();

		return $this->createResultsWithExtraResult($entities, $request, $pointer, $this->paginationLinkGenerator);
	}

}
