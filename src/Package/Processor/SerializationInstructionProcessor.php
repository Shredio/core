<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Processor;

use Shredio\Core\Package\Instruction\PackingInstruction;
use Shredio\Core\Package\Instruction\SerializationInstruction;
use Shredio\Core\Package\ItemToPack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SerializationInstructionProcessor implements InstructionProcessor
{

	public function __construct(
		private readonly NormalizerInterface $normalizer,
	)
	{
	}

	public function processItem(ItemToPack $item, PackingInstruction $instruction): ?ItemToPack
	{
		if (!$instruction instanceof SerializationInstruction) {
			return null;
		}

		return $item->withValue($this->normalizer->normalize($item->value));
	}

}
