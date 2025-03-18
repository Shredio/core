<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

use LogicException;
use Shredio\Core\Payload\ErrorPayload;
use Shredio\Core\Payload\ErrorsPayload;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @template TObject of object
 */
abstract class BaseObjectNormalizer implements NormalizerAwareInterface, NormalizerInterface, DenormalizerAwareInterface, DenormalizerInterface
{
	private const string GuardContext = 'object.recursive.guard';

	private ?DenormalizerInterface $denormalizer = null;

	private ?NormalizerInterface $normalizer = null;

	private Details $details;

	/**
	 * @return TObject
	 */
	abstract protected function toObject(DenormalizationDetails $details): ?object;

	/**
	 * @param NormalizationDetails<TObject> $details
	 * @return mixed[]
	 */
	abstract protected function toArray(NormalizationDetails $details): array;

	/**
	 * @return class-string<TObject>
	 */
	abstract protected function getObjectName(): string;

	final public function setDenormalizer(DenormalizerInterface $denormalizer): void
	{
		$this->denormalizer = $denormalizer;
	}

	final public function setNormalizer(NormalizerInterface $normalizer): void
	{
		$this->normalizer = $normalizer;
	}

	/**
	 * @phpstan-param TObject $data
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	final public function normalize(mixed $data, ?string $format = null, array $context = []): array
	{
		try {
			return $this->toArray($this->details = new NormalizationDetails($data, $format, $context));
		} finally {
			unset($this->details);
		}
	}

	/**
	 * @param mixed[] $context
	 */
	final public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
	{
		if (!is_a($data, $this->getObjectName())) {
			return false;
		}

		return $this->getGuardFromContext($context)->isOk($this->getObjectName());
	}

	/**
	 * @return array<class-string, bool>
	 */
	public function getSupportedTypes(?string $format): array
	{
		return [
			$this->getObjectName() => false,
		];
	}

	/**
	 * @phpstan-param class-string $type
	 * @param mixed[] $context
	 */
	final public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?object
	{
		if (is_object($data)) {
			return $data;
		} else if ($data === null) {
			return null;
		} else if (is_scalar($data) && $this instanceof ScalarToObjectNormalizer) {
			return $this->denormalizeScalar($data);
		}

		assert(is_array($data));

		try {
			return $this->toObject($this->details = new DenormalizationDetails($data, $type, $format, $context));
		} finally {
			unset($this->details);
		}
	}

	/**
	 * @param mixed[] $context
	 */
	final public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
	{
		if ($type !== $this->getObjectName()) {
			return false;
		}

		if ($data === null) {
			return true;
		}

		if (!is_array($data)) {
			if (is_scalar($data)) {
				if ($this instanceof ScalarToObjectNormalizer) {
					return $this->supportsScalar($data);
				} else {
					return false;
				}
			}

			$entity = $this->getObjectName();

			if ($data instanceof $entity) {
				return $this->getGuardFromContext($context)->isOk($this->getObjectName());
			}

			return false;
		}

		return $this->getGuardFromContext($context)->isOk($this->getObjectName());
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
	 * @return TObject
	 */
	protected function makeObject(array $data, ?array $context = null): object
	{
		if ($context === null) {
			$context = $this->details->getContext();
		}

		$denormalizer = $this->getDenormalizer();

		$context = $this->setGuardToContext($context);
		$this->getGuardFromContext($context)->add($this->getObjectName());

		/** @var TObject */
		return $denormalizer->denormalize($data, $this->getObjectName(), $this->details->getFormat(), $context);
	}

	/**
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	protected function makeArray(object $object, ?array $context = null): array
	{
		if ($context === null) {
			$context = $this->details->getContext();
		}

		$normalizer = $this->getNormalizer();

		$context = $this->setGuardToContext($context);
		$this->getGuardFromContext($context)->add($this->getObjectName());

		/** @var mixed[] */
		return $normalizer->normalize($object, $this->details->getFormat(), $context);
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

	protected function isCreateOperation(): bool
	{
		return !$this->isUpdateOperation();
	}

	protected function isUpdateOperation(): bool
	{
		if (!$this->details instanceof DenormalizationDetails) {
			throw new LogicException('Cannot use isCreateOperation() in normalization context.');
		}

		$objectToPopulate = $this->details->context[AbstractNormalizer::OBJECT_TO_POPULATE] ?? null;

		if (!$objectToPopulate) {
			return false;
		}

		return $objectToPopulate instanceof $this->details->type;
	}

	protected function addError(ErrorPayload $error): void
	{
		/** @var ErrorsPayload|null $errors */
		$errors = $this->details->getContext()['errors'] ?? null;

		if (!$errors) {
			return;
		}

		$errors->addError($error);
	}

}
