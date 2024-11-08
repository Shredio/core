<?php declare(strict_types = 1);

namespace Shredio\Core\Format\Attribute;

abstract readonly class FormatAttribute
{

	public const string DefaultGroup = 'default';

	/**
	 * @param string[] $groups
	 */
	public function __construct(
		public array $groups = [self::DefaultGroup],
	)
	{
	}

}
