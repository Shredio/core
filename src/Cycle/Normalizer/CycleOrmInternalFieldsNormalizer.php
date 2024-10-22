<?php declare(strict_types = 1);

namespace Shredio\Core\Cycle\Normalizer;

use ArrayObject;
use Cycle\ORM\EntityProxyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Removes Cycle ORM internal fields from serialization.
 */
final class CycleOrmInternalFieldsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{

	private const Recursion = 'cycle.orm.recursion';

	use NormalizerAwareTrait;

	/**
	 * @param mixed[] $context
	 * @return array<mixed>|string|int|float|bool|ArrayObject<array-key, mixed>|null
	 */
	public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
	{
		$context[self::Recursion] = true;

		$values = $this->normalizer->normalize($object, $format, $context);

		if (is_array($values)) {
			foreach ($values as $key => $_) {
				if (str_starts_with($key, '__cycle_')) {
					unset($values[$key]);
				}
			}
		}

		return $values;
	}

	/**
	 * @param mixed[] $context
	 */
	public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
	{
		return !isset($context[self::Recursion]) && $data instanceof EntityProxyInterface;
	}

	/**
	 * @return array<string, bool|null>
	 */
	public function getSupportedTypes(?string $format): array
	{
		return [
			'*' => false,
		];
	}

}
