<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer;

final class DenormalizationDetails implements Details
{

	/**
	 * @param mixed[] $values
	 * @param class-string $type
	 * @param mixed[] $context
	 */
	public function __construct(
		public readonly array $values,
		public readonly string $type,
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
