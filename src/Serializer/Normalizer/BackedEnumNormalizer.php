<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer as SymfonyBackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BackedEnumNormalizer implements NormalizerInterface, DenormalizerInterface
{

	/**
	 * If true, will denormalize any invalid value into null.
	 */
	public const string AllowInvalidValues = 'allow_invalid_values';
	
	private SymfonyBackedEnumNormalizer $decorate;
	
	public function __construct()
	{
		$this->decorate = new SymfonyBackedEnumNormalizer();
	}

	public function getSupportedTypes(?string $format): array
	{
		return $this->decorate->getSupportedTypes($format);
	}

	/**
	 * @param mixed[] $context
	 */
	public function normalize(mixed $object, ?string $format = null, array $context = []): int|string
	{
		return $this->decorate->normalize($object, $format, $context);
	}

	/**
	 * @param mixed[] $context
	 */
	public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
	{
		return $this->decorate->supportsNormalization($data, $format, $context);
	}

	/**
	 * @param mixed[] $context
	 */
	public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
	{
		if ($data instanceof $type) {
			return $data;
		}
		
		return $this->decorate->denormalize($data, $type, $format, $context);
	}

	/**
	 * @param mixed[] $context
	 */
	public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
	{
		return $this->decorate->supportsDenormalization($data, $type, $format, $context);
	}

}
