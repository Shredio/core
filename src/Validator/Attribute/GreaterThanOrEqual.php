<?php declare(strict_types = 1);

namespace Shredio\Core\Validator\Attribute;

use Attribute;
use Symfony\Component\Validator\Constraints as Symfony;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class GreaterThanOrEqual extends Symfony\GreaterThanOrEqual
{

	public string $message = 'greaterThanOrEqual.message';

	public function validatedBy(): string
	{
		return parent::class . 'Validator';
	}

}
