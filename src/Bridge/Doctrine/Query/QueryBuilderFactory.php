<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Query;

interface QueryBuilderFactory
{

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return QueryBuilder<T>
	 */
	public function create(string $entity): QueryBuilder;

}
