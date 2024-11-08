<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Response;

use Shredio\Core\Package\Instruction\PackingInstruction;
use Shredio\Core\Response\Response;

final class SourceResponse extends Response
{

	/**
	 * @param array<PackingInstruction|null> $instructions
	 */
	public function __construct(
		private mixed $source,
		private array $instructions,
	)
	{
		parent::__construct();
	}

	public function getSource(): mixed
	{
		return $this->source;
	}

	/**
	 * @return array<PackingInstruction|null>
	 */
	public function getInstructions(): array
	{
		return $this->instructions;
	}

}
