<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use Stringable;

/**
 * @internal
 */
final class DateTimeImmutablePrimary extends DateTimeImmutable implements Stringable
{

	public static function fromDateTime(DateTimeInterface $dateTime): self
	{
		return new self($dateTime->format('c'));
	}

	public function __toString(): string
	{
		return $this->format('c');
	}

}
