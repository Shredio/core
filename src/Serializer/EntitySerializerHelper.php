<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

use Cycle\ORM\ORMInterface;
use Spiral\Core\Attribute\Singleton;

#[Singleton]
final class EntitySerializerHelper
{

	public function __construct(
		private readonly ORMInterface $orm,
	)
	{
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entity
	 * @return T|null
	 */
	public function tryFindEntity(string $entity, mixed $value): ?object
	{
		if ($value === null) {
			return null;
		}

		if (is_a($value, $entity)) {
			return $value;
		}

		/** @var T|null */
		return $this->orm->getRepository($this->orm->resolveRole($entity))->findByPK($value); // @phpstan-ignore-line
	}

}
