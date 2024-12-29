<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CustomEndpoint extends Endpoint
{

	/**
	 * @param non-empty-array<non-empty-string> $methods
	 */
	public function __construct(
		string $route,
		private array $methods = ['GET'],
		private bool $isParametrized = false,
		private ?bool $isAbsolute = null,
	)
	{
		parent::__construct($route);
	}

	public function isParametrized(): bool
	{
		return $this->isParametrized;
	}

	public function hasAbsolutePath(): bool
	{
		if ($this->isAbsolute !== null) {
			return $this->isAbsolute;
		}

		return !$this->isParametrized;
	}

	public function getMethods(): array
	{
		return $this->methods;
	}

	public static function getDefaultMethodName(): ?string
	{
		return null;
	}

}
