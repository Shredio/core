<?php declare(strict_types = 1);

namespace Shredio\Core\Validator\Attribute;

use Attribute;
use Symfony\Component\Validator\Constraints as Symfony;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class DateTime extends Symfony\DateTime
{

	public string $message = 'datetime.message';

	public function validatedBy(): string
	{
		return parent::class . 'Validator';
	}

}
