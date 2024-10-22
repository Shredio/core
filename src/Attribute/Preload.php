<?php declare(strict_types = 1);

namespace Shredio\Core\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Preload
{

	public function __construct(
		public readonly string $role,
	)
	{
	}

}
