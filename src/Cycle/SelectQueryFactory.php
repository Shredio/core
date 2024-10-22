<?php declare(strict_types = 1);

namespace Shredio\Core\Cycle;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use RuntimeException;
use Spiral\Core\Attribute\Singleton;

#[Singleton]
final readonly class SelectQueryFactory
{

	public function __construct(
		private ORMInterface $orm,
	)
	{
	}

	/**
	 * @template TEntity of object
	 * @param class-string<TEntity> $className
	 * @return Select<TEntity>
	 */
	public function create(string $className): Select
	{
		$repository = $this->orm->getRepository($this->orm->resolveRole($className)); // @phpstan-ignore-line

		if (!$repository instanceof Select\Repository) {
			throw new RuntimeException(sprintf(
				'Repository for class %s must be an instance of %s, %s given',
				$className,
				Select\Repository::class,
				$repository::class,
			));
		}

		/** @var Select<TEntity> */
		return $repository->select();
	}

}
