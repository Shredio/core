<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
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

	/**
	 * @template T of object
	 * @param T $entity
	 * @return T
	 */
	protected function refetchEntity(object &$entity): object
	{
		$em = $this->getEntityManager($entity::class);

		$fetched = $em->find($entity::class, $em->getClassMetadata($entity::class)->getIdentifierValues($entity));
		$em->refresh($fetched);

		if (!$fetched) {
			throw new LogicException('Entity not found.');
		}

		$entity = $fetched;

		return $entity;
	}

	/**
	 * @param object|object[] $entity
	 */
	protected function persist(object|array $entity, bool $clear = true): void
	{
		$managers = [];

		if (!is_array($entity)) {
			$managers[] = $em = $this->getEntityManager($entity::class);
			$em->persist($entity);
		} else if ((array_key_first($entity)) !== null) {
			foreach ($entity as $item) {
				$className = $item::class;
				$em = $managers[$className] ??= $this->getEntityManager($className);
				$em->persist($item);
			}
		}

		foreach ($managers as $em) {
			$em->flush();

			if ($clear) {
				$em->clear();
			}
		}
	}

	protected function commitTransactionForDebugging(): void
	{
		StaticDriver::commit();
		StaticDriver::beginTransaction();
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return AssertEntity<T>
	 * @deprecated use entity() instead
	 */
	protected function assertEntity(string $entity): AssertEntity
	{
		return $this->entity($entity);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return AssertEntity<T>
	 */
	protected function entity(string $entity): AssertEntity
	{
		return new AssertEntity($entity, $this->getEntityManager($entity));
	}

}
