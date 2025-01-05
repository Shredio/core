<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

/**
 * @template T
 */
final readonly class PropertyValue
{

	/**
	 * @param T $value
	 */
	public function __construct(
		public mixed $value,
	)
	{
	}

}
