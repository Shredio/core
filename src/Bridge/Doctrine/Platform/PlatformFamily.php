<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Platform;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;

enum PlatformFamily: string
{

	case Mysql = 'mysql';
	case Sqlite = 'sqlite';
	case Postgresql = 'postgresql';
	case Unknown = 'unknown';

	public function isMysql(): bool
	{
		return $this === self::Mysql;
	}

	public function isPostgresql(): bool
	{
		return $this === self::Postgresql;
	}

	public function isSqlite(): bool
	{
		return $this === self::Sqlite;
	}

	public static function fromPlatform(AbstractPlatform $platform): self
	{
		if ($platform instanceof AbstractMySQLPlatform) {
			return self::Mysql;
		} else if ($platform instanceof PostgreSQLPlatform) {
			return self::Postgresql;
		} else if ($platform instanceof SQLitePlatform) {
			return self::Sqlite;
		} else {
			return self::Unknown;
		}
	}

	public function throwUnsupported(): never
	{
		throw new Exception\UnsupportedPlatformException($this);
	}

}
