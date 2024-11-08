<?php declare(strict_types = 1);

namespace Shredio\Core\Package\Instruction\Formatting;

final readonly class FormattingCurrency
{

	public function __construct(
		public ?string $value,
	)
	{
	}

}
