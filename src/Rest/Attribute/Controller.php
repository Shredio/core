<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

use Attribute;

/**
 * Example of usage:
 *
 *     #[RouteParameter(name: 'symbol')]
 * 	   #[Controller(name: 'metrics', id: '{symbol}')]
 *     final class MyController {}
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Controller
{

	/**
	 * @param class-string $parent
	 * @param string[] $groups
	 */
	public function __construct(
		public ?string $name = null,
		public ?string $pattern = null,
		public ?string $id = null,
		public ?string $parent = null,
		public array $groups = [],
	)
	{
	}

}
