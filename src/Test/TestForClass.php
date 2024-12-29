<?php declare(strict_types = 1);

namespace Shredio\Core\Test;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class TestForClass
{

	/**
	 * @param class-string $className
	 */
	public function __construct(
		public string $className,
	)
	{
	}

}
