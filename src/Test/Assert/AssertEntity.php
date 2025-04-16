<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Assert;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Assert;
use Shredio\Core\Struct\SinglePassIndex;

/**
 * @template T of object
 */
final readonly class AssertEntity
{

	/**
	 * @param class-string<T> $entity
	 */
	public function __construct(
		public string $entity,
		public EntityManagerInterface $em,
	)
	{
	}

	/**
	 * @return T
	 */
	public function last(): object
	{
		$orderBy = $this->em->getClassMetadata($this->entity)->getSingleIdentifierFieldName();

		$entity = $this->em->createQueryBuilder()
			->select('e')
			->from($this->entity, 'e')
			->orderBy(sprintf('e.%s', $orderBy), 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();

		if ($entity === null) {
			throw new \LogicException(sprintf('Entity %s not found.', $this->entity));
		}

		return $entity;
	}

	/**
	 * @param string[] $select fields to select
	 * @return list<array<string, mixed>>
	 */
	public function snapshot(?string $orderBy = null, bool $associations = true, array $select = []): array
	{
		$fieldsToSelect = $select ? SinglePassIndex::fromValues($select) : null;
		$metadata = $this->em->getClassMetadata($this->entity);
		$platform = $this->em->getConnection()->getDatabasePlatform();

		if ($orderBy === null) {
			$orderBy = $metadata->getSingleIdentifierFieldName();
		}

		$select = [];
		$converters = [];

		foreach ($metadata->fieldMappings as $mapping) {
			if ($fieldsToSelect?->has($mapping['fieldName']) === false) {
				continue;
			}

			$select[] = sprintf('e.%s AS %s', $mapping['fieldName'], $mapping['fieldName']);

			if ($mapping['type'] === 'uuid') {
				$type = Type::getType($mapping['type']);

				$converters[$mapping['fieldName']] = static fn (mixed $value): string => (string) $type->convertToPHPValue($value, $platform);
			}
		}

		if ($associations) {
			foreach ($metadata->associationMappings as $mapping) {
				if ($fieldsToSelect?->has($mapping['fieldName']) === false) {
					continue;
				}

				if ($mapping['type'] === ClassMetadata::MANY_TO_ONE) {
					$select[] = sprintf('IDENTITY(e.%s) AS %s', $mapping['fieldName'], $mapping['fieldName']);
				}
			}
		}

		$fieldsToSelect?->report();

		$yield = $this->em->createQueryBuilder()
			->select($select)
			->from($this->entity, 'e')
			->orderBy(sprintf('e.%s', $orderBy), 'DESC')
			->getQuery()
			->toIterable(hydrationMode: Query::HYDRATE_SCALAR);

		$results = [];

		foreach ($yield as $value) {
			foreach ($converters as $fieldName => $converter) {
				$value[$fieldName] = $converter($value[$fieldName]);
			}

			$results[] = $value;
		}

		return $results;
	}

	public function assertCount(int $expected): void
	{
		$actual = $this->em->getRepository($this->entity)->count();

		Assert::assertSame(
			$expected,
			$actual,
			sprintf('Expected %s entities in the table, actual are %s.', $expected, $actual),
		);
	}

}
