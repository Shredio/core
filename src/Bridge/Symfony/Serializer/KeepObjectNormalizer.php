<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class KeepObjectNormalizer implements DenormalizerInterface
{

	/**
	 * @param mixed[] $context
	 */
	public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
	{
		return $data;
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
		if (!is_object($data) || !str_contains($type, '\\') || str_contains($type, '[')) {
			return false;
		}

		if (!class_exists($type)) {
			return false;
		}

		return $data instanceof $type;
	}

	public function getSupportedTypes(?string $format): array
	{
		return ['*' => false];
	}

}
