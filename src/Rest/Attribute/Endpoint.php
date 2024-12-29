<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

abstract readonly class Endpoint
{

	/**
	 * @param string[] $groups
	 */
	public function __construct(
		public ?string $route = null,
		public array $groups = [],
	)
	{
	}

	public function isParametrized(): bool
	{
		return false;
	}

	public function hasAbsolutePath(): bool
	{
		return false;
	}

	/**
	 * @return non-empty-array<non-empty-string>
	 */
	abstract public function getMethods(): array;

	abstract public static function getDefaultMethodName(): ?string;

}
