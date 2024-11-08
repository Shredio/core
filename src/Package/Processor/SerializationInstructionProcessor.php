<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Processor;

use Shredio\Core\Package\Instruction\PackingInstruction;
use Shredio\Core\Package\Instruction\SerializationInstruction;
use Shredio\Core\Package\ItemToPack;
use Symfony\Component\Serializer\Serializer;

final class SerializationInstructionProcessor implements InstructionProcessor
{

	public function __construct(
		private readonly Serializer $serializer,
	)
	{
	}

	public function processItem(ItemToPack $item, PackingInstruction $instruction): ?ItemToPack
	{
		if (!$instruction instanceof SerializationInstruction) {
			return null;
		}

		return $item->withValue($this->serializer->normalize($item->value));
	}

}
