<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Type;

use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Shredio\Core\Bridge\Doctrine\DateTime\DateTimeImmutablePrimary;

final class DateImmutablePrimaryType extends DateImmutableType
{

	public const string Name = 'date_immutable_primary';

	/**
	 * @throws InvalidFormat
	 */
	public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateTimeImmutable
	{
		$value = parent::convertToPHPValue($value, $platform);

		if ($value !== null) {
			$value = DateTimeImmutablePrimary::fromDateTime($value);
		}

		return $value;
	}

}