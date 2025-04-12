<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Error;

use InvalidArgumentException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer as SymfonyProblemNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function sprintf;

final readonly class ProblemNormalizer implements NormalizerInterface, SerializerAwareInterface
{

	/**
	 * @param iterable<CustomProblemNormalizer> $normalizers
	 */
	public function __construct(
		private SymfonyProblemNormalizer $normalizer,
		private iterable $normalizers,
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
		if (!$data instanceof FlattenException) {
			throw new InvalidArgumentException(sprintf('The object must implement "%s".', FlattenException::class));
		}

		$ret = $this->normalizer->normalize($data, $format, $context);

		foreach ($this->normalizers as $normalizer) {
			$ret = $normalizer->normalize($data, $ret);
		}

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
