<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Response;

use Shredio\Core\Package\Instruction\PackingInstruction;
use Shredio\Core\Response\Response;

final class SourcesResponse extends Response
{

	/**
	 * @param iterable<mixed> $sources
	 * @param array<PackingInstruction|null> $instructions
	 */
	public function __construct(
		private iterable $sources,
		private array $instructions = [],
	)
	{
		parent::__construct();
	}

	/**
	 * @return iterable<mixed>
	 */
	public function getSources(): iterable
	{
		return $this->sources;
	}

	/**
	 * @return array<PackingInstruction|null>
	 */
	public function getInstructions(): array
	{
		return $this->instructions;
	}

}
