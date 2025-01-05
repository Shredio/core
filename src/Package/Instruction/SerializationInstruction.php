<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Instruction;

final readonly class SerializationInstruction implements PackingInstruction
{

	/**
	 * @param array<string, mixed> $context
	 */
	public function __construct(
		public array $context = [],
	)
	{
	}

}
