<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

final readonly class EntityManagerRegistry
{

	public function __construct(
		private ManagerRegistry $registry,
	)
	{
	}

	public function getDefaultManager(): EntityManagerInterface
	{
		$manager = $this->registry->getManager();

		assert($manager instanceof EntityManagerInterface);

		return $manager;
	}

	/**
	 * @return iterable<EntityManagerInterface>
	 */
	public function getManagers(): iterable
	{
		foreach ($this->registry->getManagers() as $manager) {
			assert($manager instanceof EntityManagerInterface);

			yield $manager;
		}
	}

	/**
	 * @param class-string $entity
	 */
	public function getManagerForClass(string $entity): EntityManagerInterface
	{
		$manager = $this->registry->getManagerForClass($entity);

		if ($manager === null) {
			throw new InvalidArgumentException(sprintf('Entity manager for class %s not found.', $entity));
		}

		/** @var EntityManagerInterface */
		return $manager;
	}

}
