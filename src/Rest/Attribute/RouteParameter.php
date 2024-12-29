<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class RouteParameter
{

	public function __construct(
		public string $name,
		public string|NamedPattern|null $pattern = null,
		public int|string|false|null $default = false,
	)
	{
	}

}
