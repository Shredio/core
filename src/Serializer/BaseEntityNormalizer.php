<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

use LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @template TEntity of object
 */
abstract class BaseEntityNormalizer implements NormalizerAwareInterface, NormalizerInterface, DenormalizerAwareInterface, DenormalizerInterface
{
	private const GuardContext = 'entity.recursive.guard';

	private ?DenormalizerInterface $denormalizer = null;

	private ?NormalizerInterface $normalizer = null;

	/**
	 * @param mixed[] $values
	 * @param mixed[] $context
	 * @return TEntity
	 */
	abstract protected function toEntity(array $values, ?string $format, array $context): object;

	/**
	 * @param TEntity $object
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	abstract protected function toArray(object $object, ?string $format, array $context): array;

	/**
	 * @return class-string<TEntity>
	 */
	abstract protected function getEntityName(): string;

	final public function setDenormalizer(DenormalizerInterface $denormalizer): void
	{
		$this->denormalizer = $denormalizer;
	}

	final public function setNormalizer(NormalizerInterface $normalizer): void
	{
		$this->normalizer = $normalizer;
	}

	/**
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	final public function normalize(mixed $object, ?string $format = null, array $context = []): array
	{
		return $this->toArray($object, $format, $context);
	}

	/**
	 * @param mixed[] $context
	 */
	final public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
	{
		if (!is_a($data, $this->getEntityName())) {
			return false;
		}

		return $this->getGuardFromContext($context)->isOk($this->getEntityName());
	}

	/**
	 * @return array<class-string, bool>
	 */
	public function getSupportedTypes(?string $format): array
	{
		return [
			$this->getEntityName() => false,
		];
	}

	/**
	 * @param mixed[] $context
	 */
	final public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): object
	{
		if (is_object($data)) {
			return $data;
		}

		assert(is_array($data));

		return $this->toEntity($data, $format, $context);
	}

	/**
	 * @param mixed[] $context
	 */
	final public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
	{
		if ($type !== $this->getEntityName()) {
			return false;
		}

		if (!is_array($data)) {
			$entity = $this->getEntityName();

			if ($data instanceof $entity) {
				return $this->getGuardFromContext($context)->isOk($this->getEntityName());
			}

			return false;
		}

		return $this->getGuardFromContext($context)->isOk($this->getEntityName());
	}

	/**
	 * @param mixed[] $context
	 */
	private function getGuardFromContext(array $context): SerializerRecursiveGuard
	{
		return $context[self::GuardContext] ?? new SerializerRecursiveGuard();
	}

	/**
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	private function setGuardToContext(array $context): array
	{
		$context[self::GuardContext] ??= new SerializerRecursiveGuard();

		return $context;
	}

	/**
	 * @param mixed[] $data
	 * @param mixed[] $context
	 * @return TEntity
	 */
	protected function makeEntity(array $data, array $context = [], ?string $format = null): object
	{
		$denormalizer = $this->getDenormalizer();

		$context = $this->setGuardToContext($context);
		$this->getGuardFromContext($context)->add($this->getEntityName());

		/** @var TEntity */
		return $denormalizer->denormalize($data, $this->getEntityName(), $format, $context);
	}

	/**
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	protected function makeArray(object $object, array $context = [], ?string $format = null): array
	{
		$normalizer = $this->getNormalizer();

		$context = $this->setGuardToContext($context);
		$this->getGuardFromContext($context)->add($this->getEntityName());

		/** @var mixed[] */
		return $normalizer->normalize($object, $format, $context);
	}

	protected function getNormalizer(): NormalizerInterface
	{
		if ($this->normalizer === null) {
			throw new LogicException('Normalizer is not set.');
		}

		return $this->normalizer;
	}

	protected function getDenormalizer(): DenormalizerInterface
	{
		if ($this->denormalizer === null) {
			throw new LogicException('Denormalizer is not set.');
		}

		return $this->denormalizer;
	}

	/**
	 * @param mixed[] $context
	 */
	protected function isCreateOperation(array $context): bool
	{
		return ($context['operation'] ?? null) === 'create';
	}

	/**
	 * @param mixed[] $context
	 */
	protected function isUpdateOperation(array $context): bool
	{
		return ($context['operation'] ?? null) === 'update';
	}

	/**
	 * @param mixed[] $context
	 */
	protected function isOperation(array $context): bool
	{
		return in_array($context['operation'] ?? null, ['create', 'update'], true);
	}

}
