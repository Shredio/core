<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class UpdateEndpoint extends Endpoint
{

	public function isParametrized(): bool
	{
		return true;
	}

	public function getMethods(): array
	{
		return ['PATCH'];
	}

	public static function getDefaultMethodName(): string
	{
		return 'update';
	}

}
