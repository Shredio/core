<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

interface ScalarToObjectNormalizer
{

	public function supportsScalar(string|int|bool|float $value): bool;

	public function denormalizeScalar(string|int|bool|float $value): ?object;

}
