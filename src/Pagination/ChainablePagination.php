<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

interface ChainablePagination extends Pagination
{

	/**
	 * @param iterable<mixed> $results
	 */
	public function supports(iterable $results): bool;

}
