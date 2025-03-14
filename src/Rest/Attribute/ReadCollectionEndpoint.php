<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class ReadCollectionEndpoint extends Endpoint
{

	public function __construct(
		?string $route = null,
		array $groups = [],
		private bool $isParametrized = false,
	)
	{
		parent::__construct($route, $groups);
	}

	public function isParametrized(): bool
	{
		return $this->isParametrized;
	}

	public function getMethods(): array
	{
		return ['GET'];
	}

	public static function getDefaultMethodName(): string
	{
		return 'index';
	}

}
