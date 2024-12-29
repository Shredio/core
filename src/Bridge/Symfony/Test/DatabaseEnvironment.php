<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
	 * @param object|object[] $entity
	 */
	protected function persist(object|array $entity): void
	{
		if (!is_array($entity)) {
			$em = $this->getEntityManager($entity::class);
			$em->persist($entity);
			$em->flush();
		} else if (($firstKey = array_key_first($entity)) !== null) {
			$className = $entity[$firstKey]::class;
			$em = $this->getEntityManager($className);

			foreach ($entity as $item) {
				$em->persist($item);
			}

			$em->flush();
		}
	}

	protected function assertEntity(string $entity): AssertEntity
	{
		return new AssertEntity($entity, $this->getEntityManager($entity));
	}

}
