<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class ReadEndpoint extends Endpoint
{

	/**
	 * @param string[] $groups
	 */
	public function __construct(
		?string $route = null,
		array $groups = [],
	)
	{
		parent::__construct($route, $groups);
	}

	public function isParametrized(): bool
	{
		return true;
	}

	public function getMethods(): array
	{
		return ['GET'];
	}

	public static function getDefaultMethodName(): string
	{
		return 'read';
	}

}
