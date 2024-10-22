<?php declare(strict_types = 1);

namespace Shredio\Core\Entity\Metadata;

abstract readonly class Context
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
