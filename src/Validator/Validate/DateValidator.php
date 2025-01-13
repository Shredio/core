<?php declare(strict_types = 1);

namespace Shredio\Core\Validator\Validate;

use DateTimeImmutable;
use Shredio\Core\Validator\Attribute\Date;
use Stringable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class DateValidator extends ConstraintValidator
{

	/** @var string[] */
	private array $formats = [
		DateTimeImmutable::RFC3339_EXTENDED, // javascript ISO date format
		'Y-m-d H:i:s',
		'Y-m-d',
	];

	public function validate(mixed $value, Constraint $constraint): void
	{
		if (!$constraint instanceof Date) {
			throw new UnexpectedTypeException($constraint, Date::class);
		}

		if (null === $value || '' === $value) {
			return;
		}

		if (!is_scalar($value) && !$value instanceof Stringable) {
			throw new UnexpectedValueException($value, 'string');
		}

		$value = (string) $value;

		foreach ($this->formats as $format) {
			$date = DateTimeImmutable::createFromFormat($format, $value);

			if ($date) {
				return;
			}
		}

		$this->context->buildViolation($constraint->message)
			->setParameter('{{ value }}', $this->formatValue($value))
			->addViolation();
	}

}
