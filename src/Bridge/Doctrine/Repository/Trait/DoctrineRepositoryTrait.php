<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Repository\Trait;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilder;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryHelper;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryServices;
use Shredio\Core\Bridge\Doctrine\Schema\SchemaResolver;
use Shredio\RapidDatabaseOperations\RapidOperationFactory;

trait DoctrineRepositoryTrait
{

	protected readonly RapidOperationFactory $rapidOperationFactory;

	protected readonly DoctrineRepositoryHelper $helper;

	public function __construct(
		private readonly DoctrineRepositoryServices $services,
	)
	{
		$this->rapidOperationFactory = $services->rapidOperationFactory;
		$this->helper = $this->services->helper;
	}

	/**
	 * @param class-string $entity
	 */
	protected function getSchemaResolver(string $entity): SchemaResolver
	{
		return new SchemaResolver($this->getEntityManager($entity)->getClassMetadata($entity));
	}

	/**
	 * @param class-string $entity
	 */
	protected function getConnection(string $entity): Connection
	{
		return $this->getEntityManager($entity)->getConnection();
	}

	/**
	 * @param class-string $entity
	 */
	protected function getEntityManager(string $entity): EntityManagerInterface
	{
		return $this->helper->getEntityManagerFor($entity);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return ClassMetadata<T>
	 */
	protected function getClassMetadata(string $entity): ClassMetadata
	{
		return $this->getEntityManager($entity)->getClassMetadata($entity);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return QueryBuilder<T>
	 */
	protected function createQueryBuilder(string $entity): QueryBuilder
	{
		return $this->services->queryBuilderFactory->create($entity);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return T|null
	 */
	protected function findByPrimary(string $entity, mixed $id): ?object
	{
		return $this->getEntityManager($entity)->find($entity, $id);
	}

}
