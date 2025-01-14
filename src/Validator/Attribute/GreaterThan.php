<?php declare(strict_types = 1);

namespace Shredio\Core\Validator\Attribute;

use Attribute;
use Symfony\Component\Validator\Constraints as Symfony;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class GreaterThan extends Symfony\GreaterThan
{

	public string $message = 'greaterThan.message';

	public function validatedBy(): string
	{
		return parent::class . 'Validator';
	}

}
