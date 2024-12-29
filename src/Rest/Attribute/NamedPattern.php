<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Attribute;

final class NamedPattern
{

	/**
	 * @param non-empty-string $name
	 */
	public function __construct(
		public string $name,
	)
	{
	}

}
