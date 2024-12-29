<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class DeleteEndpoint extends Endpoint
{

	public function isParametrized(): bool
	{
		return true;
	}

	public function getMethods(): array
	{
		return ['DELETE'];
	}

	public static function getDefaultMethodName(): string
	{
		return 'delete';
	}

}
