<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Serializer;

use Shredio\Core\Security\AccountId;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AccountIdNormalizer implements DenormalizerInterface, NormalizerInterface
{

	/**
	 * @param mixed[] $context
	 */
	public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): AccountId
	{
		if ($data instanceof AccountId) {
			return $data;
		}

		return AccountId::from($data);
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
		return $type === AccountId::class;
	}

	/**
	 * @param mixed[] $context
	 */
	public function normalize(
		mixed $data,
		?string $format = null,
		array $context = [],
	): string|int
	{
		assert($data instanceof AccountId);

		return $data->toOriginal();
	}

	/**
	 * @param mixed[] $context
	 */
	public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
	{
		return $data instanceof AccountId;
	}

	public function getSupportedTypes(?string $format): array
	{
		return [AccountId::class => false];
	}

}
