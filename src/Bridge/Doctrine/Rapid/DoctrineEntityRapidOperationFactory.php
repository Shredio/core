<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Rapid;

use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Database\Rapid\EntityRapidOperationFactory;
use Shredio\Core\Database\Rapid\RapidInserter;
use Shredio\Core\Database\Rapid\RapidUpdater;

final readonly class DoctrineEntityRapidOperationFactory implements EntityRapidOperationFactory
{

	public function __construct(
		private EntityManagerRegistry $registry,
	)
	{
	}

	/**
	 * @param class-string $entity
	 * @param string[] $conditions
	 */
	public function createBigUpdate(string $entity, array $conditions): RapidUpdater
	{
		return new DoctrineRapidBigUpdater(
			$entity,
			$conditions,
			$this->registry->getManagerForClass($entity),
		);
	}

	/**
	 * @param class-string $entity
	 * @param string[] $conditions fields used for conditions e.g. ['id']
	 */
	public function createUpdate(string $entity, array $conditions): RapidUpdater
	{
		return new DoctrineRapidUpdater($entity, $conditions, $this->registry->getManagerForClass($entity));
	}

	/**
	 * @param class-string $entity
	 * @param string[] $columnsToUpdate
	 */
	public function createUpsert(string $entity, array $columnsToUpdate = []): RapidInserter
	{
		return new DoctrineRapidInserter($entity, $this->registry->getManagerForClass($entity), [
			DoctrineRapidInserter::ColumnsToUpdate => $columnsToUpdate,
			DoctrineRapidInserter::Mode => DoctrineRapidInserter::ModeUpsert,
		]);
	}

	/**
	 * @param class-string $entity
	 */
	public function createInsert(string $entity): RapidInserter
	{
		return new DoctrineRapidInserter($entity, $this->registry->getManagerForClass($entity));
	}

}
