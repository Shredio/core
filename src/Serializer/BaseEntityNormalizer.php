<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Serializer\Argument\ObjectNormalizerServices;

/**
 * @template TEntity of object
 * @extends BaseObjectNormalizer<TEntity>
 */
abstract class BaseEntityNormalizer extends BaseObjectNormalizer implements ScalarToObjectNormalizer
{

	protected readonly EntityManagerRegistry $managerRegistry;

	public function __construct(
		ObjectNormalizerServices $services,
	)
	{
		$this->managerRegistry = $services->managerRegistry;
	}

	public function supportsScalar(float|bool|int|string $value): bool
	{
		return is_string($value) || is_bool($value);
	}

	/**
	 * @return TEntity|null
	 */
	public function denormalizeScalar(float|bool|int|string $value): ?object
	{
		return $this->managerRegistry->getManagerForClass($this->getObjectName())->find($this->getObjectName(), $value);
	}

}
