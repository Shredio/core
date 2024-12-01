<?php declare(strict_types = 1);

namespace Shredio\Core\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class PreValidate
{

	/**
	 * @param string[]|null $groups
	 */
	public function __construct(
		public ?array $groups = null,
	)
	{
	}

}
