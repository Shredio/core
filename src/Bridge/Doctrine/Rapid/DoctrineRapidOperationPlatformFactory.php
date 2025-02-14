<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Rapid;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use InvalidArgumentException;
use Shredio\Core\Database\Rapid\Platform\MysqlRapidOperationPlatform;
use Shredio\Core\Database\Rapid\Platform\RapidOperationPlatform;
use Shredio\Core\Database\Rapid\Platform\SqliteRapidOperationPlatform;

final class DoctrineRapidOperationPlatformFactory
{

	public static function create(AbstractPlatform $platform): RapidOperationPlatform
	{
		if ($platform instanceof MySQLPlatform) {
			return new MysqlRapidOperationPlatform();
		}

		if ($platform instanceof SQLitePlatform) {
			return new SqliteRapidOperationPlatform();
		}

		throw new InvalidArgumentException(sprintf('Unsupported platform %s', $platform::class));
	}

}
