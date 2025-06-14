<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Nette\Utils\FileSystem;
use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Bridge\Doctrine\Platform\PlatformFamily;
use Shredio\Core\Bridge\Doctrine\Repository\Criteria\CriteriaParser;
use Shredio\Core\Bridge\Doctrine\Result\DatabaseFieldResult;
use Shredio\Core\Bridge\Doctrine\Result\DatabasePairsResult;
use Shredio\Core\Bridge\Doctrine\Result\DatabaseResult;
use Symfony\Contracts\Service\ResetInterface;

final class DoctrineRepositoryHelper implements ResetInterface
{

	/** @var array<string, EntityManagerInterface> */
	private array $cache = [];

	/** @var array<string, EntityRepository<object>> */
	private array $repositories = [];

	/** @var array<string, ClassMetadata<object>> */
	private array $metadata = [];

	public function __construct(
		private readonly EntityManagerRegistry $managerRegistry,
	)
	{
	}

	/**
	 * Finds entities by a set of criteria.
	 *
	 * @template T of object
	 * @param class-string<T> $entity
	 * @param array<string, mixed> $criteria
	 * @param array<string, 'ASC'|'DESC'>|null $orderBy
	 * @return list<T>
	 */
	public function findBy(string $entity, array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
	{
		/** @var list<T> */
		return $this->getRepository($entity)->findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * Finds entities by a set of IDs.
	 *
	 * @template T of object
	 * @param class-string<T> $entity
	 * @param mixed[] $ids
	 * @param array<string, 'ASC'|'DESC'>|null $orderBy
	 * @return list<T>
	 */
	public function findById(string $entity, array $ids, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
	{
		$primaryId = $this->getMetadata($entity)->getSingleIdentifierFieldName();

		return $this->findBy($entity, [$primaryId => $ids], $orderBy, $limit, $offset);
	}

	/**
	 * Finds a single entity by a set of criteria.
	 *
	 * @template T of object
	 * @param class-string<T> $entity
	 * @param array<string, mixed> $criteria
	 * @param array<string, 'ASC'|'DESC'>|null $orderBy
	 * @return T|null
	 */
	public function findOneBy(string $entity, array $criteria, ?array $orderBy = null): ?object
	{
		/** @var T|null */
		return $this->getRepository($entity)->findOneBy($criteria, $orderBy);
	}

	/**
	 * Counts entities by a set of criteria.
	 *
	 * @param class-string $entity
	 * @param array<string, mixed> $criteria
	 * @return int<0, max>
	 */
	public function count(string $entity, array $criteria = []): int
	{
		return $this->getRepository($entity)->count($criteria);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @param string[] $select
	 * @return DatabaseResult<T>
	 */
	public function fetchAll(string $entity, array $select = []): DatabaseResult
	{
		$qb = $this->getEntityManagerFor($entity)->createQueryBuilder();
		$qb->from($entity, 'e');

		if ($select) {
			$qb->select(...$this->createSelectStatement($entity, $select, 'e'));
		} else {
			$qb->select('e');
		}

		return new DatabaseResult($qb->getQuery(), $entity);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @param array<string, mixed> $criteria
	 * @param array<string, 'ASC'|'DESC'> $orderBy
	 * @param string[] $select
	 * @return DatabaseResult<T>
	 */
	public function fetchBy(string $entity, array $criteria = [], array $orderBy = [], array $select = []): DatabaseResult
	{
		$qb = $this->getEntityManagerFor($entity)->createQueryBuilder();
		$qb->from($entity, 'e');

		if ($select) {
			$qb->select(...$this->createSelectStatement($entity, $select, 'e'));
		} else {
			$qb->select('e');
		}

		$this->applyCriteria($qb, $criteria, 'e');
		$this->applyOrderBy($qb, $orderBy);

		return new DatabaseResult($qb->getQuery(), $entity, (bool) $select);
	}

	/**
	 * Retrieves key-value pairs from the database for the specified entity.
	 *
	 * @param class-string $entity The class of the entity to fetch the pairs from.
	 * @param string $key The field to use as the key in the resulting pairs.
	 * @param string $value The field to use as the value in the resulting pairs.
	 * @param array<string, mixed> $criteria Optional criteria to filter the query.
	 */
	public function fetchPairs(string $entity, string $key, string $value, array $criteria = []): DatabasePairsResult
	{
		$qb = $this->getEntityManagerFor($entity)->createQueryBuilder();
		$qb->from($entity, 'e')
			->select(...$this->createSelectStatement($entity, [$key => 'k', $value => 'v'], 'e'));

		$this->applyCriteria($qb, $criteria, 'e');

		return new DatabasePairsResult($qb->getQuery(), 'k', 'v');
	}

	/**
	 * Fetches a specific field from the database based on the given entity, field, and criteria.
	 *
	 * @param class-string $entity The class of the entity to fetch the field from.
	 * @param string $field The specific field to retrieve from the entity.
	 * @param array<string, mixed> $criteria Optional criteria to filter the query.
	 * @param bool $distinct Whether to return distinct values. USE as named argument.
	 */
	public function fetchField(string $entity, string $field, array $criteria = [], bool $distinct = false): DatabaseFieldResult
	{
		$qb = $this->getEntityManagerFor($entity)->createQueryBuilder();
		$qb->from($entity, 'e')
			->select(...$this->createSelectStatement($entity, [$field => 'f'], 'e'));

		if ($distinct) {
			$qb->distinct();
		}

		$this->applyCriteria($qb, $criteria, 'e');

		return new DatabaseFieldResult($qb->getQuery(), 'f');
	}

	/**
	 * Fetches a specific field from the database based on the given entity, field, and criteria.
	 *
	 * @param class-string $entity The class of the entity to fetch the field from.
	 * @param string $field The specific field to retrieve from the entity.
	 * @param array<string, mixed> $criteria Criteria to filter the query.
	 */
	public function fetchSingleField(string $entity, string $field, array $criteria): mixed
	{
		$qb = $this->getEntityManagerFor($entity)->createQueryBuilder();
		$qb->from($entity, 'e')
			->select(...$this->createSelectStatement($entity, [$field => 'f'], 'e'));

		$this->applyCriteria($qb, $criteria, 'e');

		try {
			return $qb->getQuery()->getSingleScalarResult();
		} catch (NoResultException) {
			return null;
		}
	}

	/**
	 * @param class-string $entity
	 * @param string[] $fields
	 * @param string $entityAlias
	 * @return string[]
	 */
	private function createSelectStatement(string $entity, array $fields, string $entityAlias): array
	{
		$return = [];
		$metadata = $this->getMetadata($entity);

		foreach ($fields as $field => $alias) {

			if (is_int($field)) {
				// alias is field
				$assoc = $metadata->hasAssociation($alias);
				$return[] = $assoc ? sprintf('IDENTITY(%s.%s) AS %s', $entityAlias, $alias, $alias) : sprintf('%s.%s', $entityAlias, $alias);
			} else {
				$assoc = $metadata->hasAssociation($field);
				$return[] = $assoc ? sprintf('IDENTITY(%s.%s) AS %s', $entityAlias, $field, $alias) : sprintf('%s.%s AS %s', $entityAlias, $field, $alias);
			}
		}

		return $return;
	}

	/**
	 * Determines if an entity exists in the database based on the given criteria.
	 *
	 * @param class-string $entity The class of the entity to check for existence.
	 * @param array<string, mixed> $criteria The criteria used to filter the query.
	 *
	 * @return bool True if an entity matching the criteria exists, otherwise false.
	 */
	public function exists(string $entity, array $criteria): bool
	{
		$qb = $this->getEntityManagerFor($entity)->createQueryBuilder();
		$qb->from($entity, 'e');
		$qb->select('1');

		$this->applyCriteria($qb, $criteria, 'e');

		$qb->setMaxResults(1);

		return (bool) $qb->getQuery()->getOneOrNullResult();
	}

	/**
	 * @param class-string $entity
	 */
	public function getEntityManagerFor(string $entity): EntityManagerInterface
	{
		return $this->cache[$entity] ??= $this->createEntityManagerFor($entity);
	}

	/**
	 * @internal
	 */
	public function reset(): void
	{
		$this->cache = [];
		$this->repositories = [];
		$this->metadata = [];
	}

	/**
	 * @param array<string, mixed> $criteria
	 */
	private function applyCriteria(QueryBuilder $qb, array $criteria, string $alias): void
	{
		foreach (CriteriaParser::parse($criteria) as $parsed) {
			$qb->andWhere($alias . '.' . $parsed->getExpression());

			if ($parsed->parameterName) {
				$qb->setParameter($parsed->parameterName, $parsed->value);
			}
		}
	}

	/**
	 * @param class-string $entity
	 */
	private function createEntityManagerFor(string $entity): EntityManagerInterface
	{
		return $this->managerRegistry->getManagerForClass($entity);
	}

	/**
	 * @param class-string $entityForConnection
	 * @param array<string, mixed> $parameters
	 * @param array<int<0, max>, string|Type|ParameterType|ArrayParameterType>|array<string, string|Type|ParameterType|ArrayParameterType> $types
	 */
	public function executeRawQueryFromFile(string $entityForConnection, string $fileName, array $parameters = [], array $types = []): Result
	{
		$connection = $this->getEntityManagerFor($entityForConnection)->getConnection();
		if (str_ends_with($fileName, '.php')) {
			$sql = $this->loadSqlFile($fileName, PlatformFamily::fromPlatform($connection->getDatabasePlatform()));
		} else {
			$sql = FileSystem::read($fileName);
		}

		return $connection->executeQuery($sql, $parameters, $types);
	}

	private function loadSqlFile(string $script, PlatformFamily $platform): string
	{
		return (static function () use ($script, $platform): string { // @phpstan-ignore-line
			return require $script;
		})();
	}

	/**
	 * @param QueryBuilder $qb
	 * @param array<string, 'ASC'|'DESC'> $orderBy
	 */
	private function applyOrderBy(QueryBuilder $qb, array $orderBy): void
	{
		foreach ($orderBy as $field => $direction) {
			$qb->addOrderBy(sprintf('e.%s', $field), $direction);
		}
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return EntityRepository<T>
	 */
	private function getRepository(string $entity): EntityRepository
	{
		/** @var EntityRepository<T> */
		return $this->repositories[$entity] ??= $this->getEntityManagerFor($entity)->getRepository($entity);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return ClassMetadata<T>
	 */
	private function getMetadata(string $entity): ClassMetadata
	{
		/** @var ClassMetadata<T> */
		return $this->metadata[$entity] ??= $this->getEntityManagerFor($entity)->getClassMetadata($entity);
	}

}
