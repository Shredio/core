<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Processor;

use Shredio\Core\Format\Attribute\MoneyFormat;
use Shredio\Core\Format\ValuesFormatter;
use Shredio\Core\Package\Instruction\FormattingInstruction;
use Shredio\Core\Package\Instruction\PackingInstruction;
use Shredio\Core\Package\ItemToPack;

final readonly class FormattingInstructionProcessor implements InstructionProcessor
{

	public function __construct(
		private ValuesFormatter $valuesFormatter,
	)
	{
	}

	public function processItem(ItemToPack $item, PackingInstruction $instruction): ?ItemToPack
	{
		if (!$instruction instanceof FormattingInstruction) {
			return null;
		}

		$context = $instruction->context;

		if ($instruction->groups) {
			$context[ValuesFormatter::GroupsContext] = $instruction->groups;
		}

		if ($instruction->currency !== false) {
			$context[MoneyFormat::CurrencyInContext] = $instruction->currency;
		}

		return $item->withValue($this->valuesFormatter->format($item->value, $instruction->className, $context));
	}

}
