<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Repository\Trait;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilder;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryHelper;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryServices;
use Shredio\Core\Bridge\Doctrine\Schema\SchemaResolver;
use Shredio\Core\Exception\RecordNotFoundException;
use Shredio\RapidDatabaseOperations\RapidOperationFactory;

/**
 * @template TEntity of object
 * @template TException of RecordNotFoundException
 * @template TId
 */
trait DoctrineEntityRepositoryTrait
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
	 * @param TId $id
	 * @return TEntity|null
	 */
	public function find(mixed $id): ?object
	{
		return $this->getEntityManager()->find($this->entity(), $id);
	}

	/**
	 * @param TId[] $ids
	 * @return TEntity[]
	 */
	public function findMany(array $ids): array
	{
		return $this->helper->findById($this->entity(), $ids);
	}

	/**
	 * @param TId $id
	 * @return TEntity
	 * @throws TException
	 */
	public function findStrict(mixed $id): object
	{
		$entity = $this->find($id);

		if ($entity === null) {
			throw $this->createNotFoundException($this->stringifyId($id));
		}

		return $entity;
	}

	/**
	 * @param TEntity|null $entity
	 */
	public function remove(?object $entity): void
	{
		if ($entity === null) {
			return;
		}

		$em = $this->getEntityManager();
		$em->remove($entity);
		$em->flush();
	}

	/**
	 * @param TEntity|null $entity
	 */
	public function save(?object $entity): void
	{
		if ($entity === null) {
			return;
		}

		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush();
	}

	/**
	 * @return TException
	 */
	abstract protected function createNotFoundException(string $id): RecordNotFoundException;

	/**
	 * @return class-string<TEntity>
	 */
	abstract protected function entity(): string;

	/**
	 * @return QueryBuilder<TEntity>
	 */
	protected function from(string $alias): QueryBuilder
	{
		$entity = $this->entity();

		return $this->createQueryBuilder($entity)
			->select($alias)
			->from($entity, $alias);
	}

	protected function getSchemaResolver(): SchemaResolver
	{
		return new SchemaResolver($this->getClassMetadata());
	}

	protected function getConnection(): Connection
	{
		return $this->getEntityManager()->getConnection();
	}

	protected function getEntityManager(): EntityManagerInterface
	{
		return $this->helper->getEntityManagerFor($this->entity());
	}

	/**
	 * @return ClassMetadata<TEntity>
	 */
	protected function getClassMetadata(): ClassMetadata
	{
		return $this->getEntityManager()->getClassMetadata($this->entity());
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

	protected function stringifyId(mixed $id): string
	{
		return (string) $id;
	}

}
