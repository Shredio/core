<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Serializer;

use Shredio\Core\Struct\Symbol;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SymbolNormalizer implements NormalizerInterface, DenormalizerInterface
{

	/**
	 * @param mixed $data
	 * @param mixed[] $context
	 */
	public function normalize(
		mixed $data,
		?string $format = null,
		array $context = [],
	): string
	{
		assert($data instanceof Symbol);

		return $data->value;
	}

	/**
	 * @param mixed[] $context
	 */
	public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
	{
		return $data instanceof Symbol;
	}

	/**
	 * @param mixed[] $context
	 */
	public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
	{
		return new Symbol($data);
	}

	/**
	 * @param mixed[] $context
	 */
	public function supportsDenormalization(
		mixed $data,
		string $type,
		?string $format = null,
		array $context = [],
	): bool
	{
		return $type === Symbol::class && is_string($data);
	}

	/**
	 * @return array<class-string, bool>
	 */
	public function getSupportedTypes(?string $format): array
	{
		return [
			Symbol::class => false,
		];
	}

}
