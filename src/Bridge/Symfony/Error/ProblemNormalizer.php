<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Error;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer as SymfonyProblemNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ProblemNormalizer implements NormalizerInterface, SerializerAwareInterface
{

	public function __construct(
		private SymfonyProblemNormalizer $normalizer,
	)
	{
	}

	/**
	 * @param mixed $data
	 * @param mixed[] $context
	 * @return mixed[]
	 */
	public function normalize(
		mixed $data,
		?string $format = null,
		array $context = [],
	): array
	{
		$ret = $this->normalizer->normalize($data, $format, $context);

		unset($ret[SymfonyProblemNormalizer::TYPE]);
		unset($ret[SymfonyProblemNormalizer::TITLE]);

		return $ret;
	}

	/**
	 * @param mixed[] $context
	 */
	public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
	{
		return $this->normalizer->supportsNormalization($data, $format, $context);
	}

	public function getSupportedTypes(?string $format): array
	{
		return $this->normalizer->getSupportedTypes($format);
	}

	public function setSerializer(SerializerInterface $serializer): void
	{
		$this->normalizer->setSerializer($serializer);
	}

}
