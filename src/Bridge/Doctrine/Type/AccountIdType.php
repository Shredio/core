<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Shredio\Core\Security\AccountId;

final class AccountIdType extends Type
{

	public const string Name = 'account_id';

	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return $platform->getIntegerTypeDeclarationSQL($column);
	}

	public function convertToPHPValue($value, AbstractPlatform $platform): mixed
	{
		if (is_int($value)) {
			return AccountId::from($value);
		}

		return parent::convertToPHPValue($value, $platform);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
	{
		if ($value instanceof AccountId) {
			return $value->toOriginal();
		}

		return parent::convertToDatabaseValue($value, $platform);
	}

	public function getName(): string
	{
		return self::Name;
	}

	public function getBindingType(): ParameterType
	{
		return ParameterType::INTEGER;
	}

}
