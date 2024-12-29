<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Pagination;

use InvalidArgumentException;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilder;
use Shredio\Core\Pagination\PaginationFactory;

final class DoctrinePaginationFactory implements PaginationFactory
{

	public function create(iterable $values, int $limit, ?int $page): iterable
	{
		if (!$values instanceof QueryBuilder) {
			throw new InvalidArgumentException(sprintf('Expected %s, got %s', QueryBuilder::class, get_debug_type($values)));
		}

		$values->setMaxResults($limit);
		$values->setFirstResult(($page - 1) * $limit);

		return $values;
	}

}
