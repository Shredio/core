<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Query;

use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;

final readonly class DefaultQueryBuilderFactory implements QueryBuilderFactory
{

	public function __construct(
		private EntityManagerRegistry $registry,
	)
	{
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return QueryBuilder<T>
	 */
	public function create(string $entity): QueryBuilder
	{
		/** @var QueryBuilder<T> */
		return new QueryBuilder($this->registry->getManagerForClass($entity));
	}

}
