<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

/**
 * @template T of object
 */
final class NormalizationDetails implements Details
{

	/**
	 * @param T $object
	 * @param mixed[] $context
	 */
	public function __construct(
		public readonly object $object,
		public readonly ?string $format,
		public array $context,
	)
	{
	}

	/**
	 * @return mixed[]
	 */
	public function &getContext(): array
	{
		return $this->context;
	}

	public function getFormat(): ?string
	{
		return $this->format;
	}

}
