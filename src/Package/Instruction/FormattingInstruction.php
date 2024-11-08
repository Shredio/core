<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Instruction;

class FormattingInstruction implements PackingInstruction
{

	/**
	 * @param class-string $className
	 * @param mixed[] $context
	 * @param string[] $groups
	 */
	public function __construct(
		public readonly string $className,
		public readonly array $context = [],
		public readonly string|null|false $currency = false,
		public readonly array $groups = ['default'],
	)
	{
	}

}
