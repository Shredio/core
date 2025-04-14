<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use SensitiveParameter;

final class SchemaDriver extends AbstractDriverMiddleware
{

	/** @var array<string, boolean> */
	private static array $created = [];

	public function __construct(
		Driver $wrappedDriver,
		private readonly ManagerRegistry $registry,
	)
	{
		parent::__construct($wrappedDriver);
	}

	public function connect(#[SensitiveParameter] array $params): DriverConnection
	{
		try {
			$connection = parent::connect($params);
		} catch (Driver\Exception $exception) {
			if ($exception->getCode() === 1049) { // unknown database
				$dbname = $params['dbname'] ?? null;

				assert(is_string($dbname));

				unset($params['dbname']);

				$connection = $this->createConnectionWithDatabase(parent::connect($params), $dbname);
			} else {
				throw $exception;
			}
		}

		/** @var string|null $connectionKey */
		$connectionKey = $params['dama.connection_key'] ?? null; // @phpstan-ignore nullCoalesce.offset

		if ($connectionKey && !isset(self::$created[$connectionKey])) {
			self::$created[$connectionKey] = true;

			$connection->exec($this->getSql($connectionKey));
		}

		return $connection;
	}

	private function getSql(string $managerName): string
	{
		$em = $this->registry->getManager($managerName);

		assert($em instanceof EntityManagerInterface);

		$metadataClasses = $em->getMetadataFactory()->getAllMetadata();

		$dropSql = $this->getDropSchemaSql($metadataClasses);
		$createSql = (new SchemaTool($em))->getCreateSchemaSql($metadataClasses);

		return sprintf("%s;\n%s;", $dropSql, implode(";\n", $createSql));
	}

	/**
	 * @param ClassMetadata<object>[] $metadataClasses
	 */
	private function getDropSchemaSql(array $metadataClasses): string
	{
		$sql = "SET FOREIGN_KEY_CHECKS=0;\n";

		foreach ($metadataClasses as $class) {
			$sql .= sprintf("DROP TABLE IF EXISTS `%s`;\n", $class->getTableName());
		}

		$sql .= "SET FOREIGN_KEY_CHECKS=1";

		return $sql;
	}

	private function createConnectionWithDatabase(DriverConnection $connection, string $dbname): DriverConnection
	{
		$connection->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s`; USE `%s`', $dbname, $dbname));

		return $connection;
	}

}
