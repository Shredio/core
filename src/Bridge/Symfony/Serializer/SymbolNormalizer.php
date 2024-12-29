<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Serializer;

use Shredio\Core\Struct\Symbol;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SymbolNormalizer implements NormalizerInterface
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
	 * @return array<class-string, bool>
	 */
	public function getSupportedTypes(?string $format): array
	{
		return [
			Symbol::class => false,
		];
	}

}
