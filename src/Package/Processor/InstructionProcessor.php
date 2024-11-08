<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Processor;

use Shredio\Core\Package\Instruction\PackingInstruction;
use Shredio\Core\Package\ItemToPack;

interface InstructionProcessor
{

	public function processItem(ItemToPack $item, PackingInstruction $instruction): ?ItemToPack;

}
