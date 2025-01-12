<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use Shredio\Core\Test\Assert\AssertEntity;

trait DatabaseEnvironment // @phpstan-ignore-line
{

	use KernelEnvironment;

	protected function getEntityManager(?string $entity = null): EntityManagerInterface
	{
		return $entity ? $this->getManagerRegistry()->getManagerForClass($entity) : $this->getManagerRegistry()->getManager();
	}

	protected function getManagerRegistry(): ManagerRegistry
	{
		/** @var ManagerRegistry */
		return $this->getContainer()->get('doctrine');
	}

	protected function refetchRelationship(object &$entity): object
	{
		$em = $this->getEntityManager($entity::class);
		$fetched = $em->find($entity::class, $em->getClassMetadata($entity::class)->getIdentifierValues($entity));

		if (!$fetched) {
			throw new LogicException('Entity not found.');
		}

		$entity = $fetched;

		return $entity;
	}

	/**
	 * @param object|object[] $entity
	 */
	protected function persist(object|array $entity): void
	{
		if (!is_array($entity)) {
			$em = $this->getEntityManager($entity::class);
			$em->persist($entity);
			$em->flush();
		} else if (($firstKey = array_key_first($entity)) !== null) {
			$managers = [];

			foreach ($entity as $item) {
				$className = $item::class;
				$em = $managers[$className] ??= $this->getEntityManager($className);
				$em->persist($item);
			}

			foreach ($managers as $em) {
				$em->flush();
			}
		}
	}

	protected function assertEntity(string $entity): AssertEntity
	{
		return new AssertEntity($entity, $this->getEntityManager($entity));
	}

}
