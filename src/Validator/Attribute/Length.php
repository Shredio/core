<?php declare(strict_types = 1);

namespace Shredio\Core\Validator\Attribute;

use Attribute;
use Symfony\Component\Validator\Constraints as Symfony;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Length extends Symfony\Length
{

	public string $maxMessage = 'length.max';
	public string $minMessage = 'length.min';
	public string $exactMessage = 'length.exact';
	public string $charsetMessage = 'length.charset';

	public function validatedBy(): string
	{
		return parent::class . 'Validator';
	}

}
