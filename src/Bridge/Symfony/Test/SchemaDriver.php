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

	private const string DisableForeignKeyChecks = "SET FOREIGN_KEY_CHECKS=0;\n";
	private const string EnableForeignKeyChecks = "SET FOREIGN_KEY_CHECKS=1;\n";

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
		$connection = $this->registry->getConnection($managerName);
		$managers = [];

		foreach ($this->registry->getManagers() as $manager) {
			if ($manager instanceof EntityManagerInterface && $manager->getConnection() === $connection) {
				$managers[] = $manager;
			}
		}

		$dropSql = '';
		$createSql = '';

		foreach ($managers as $em) {
			$metadataClasses = $em->getMetadataFactory()->getAllMetadata();

			$dropSql .= $this->getDropSchemaSql($metadataClasses);
			$createSql .= $this->getCreateSchemaSql($em, $metadataClasses);
		}

		return self::DisableForeignKeyChecks . $dropSql . self::EnableForeignKeyChecks . $createSql;
	}

	/**
	 * @param list<ClassMetadata<object>> $metadataClasses
	 */
	private function getCreateSchemaSql(EntityManagerInterface $em, array $metadataClasses): string
	{
		$list = (new SchemaTool($em))->getCreateSchemaSql($metadataClasses);

		if (!$list) {
			return '';
		}

		return implode(";\n", $list) . ";\n";
	}

	/**
	 * @param list<ClassMetadata<object>> $metadataClasses
	 */
	private function getDropSchemaSql(array $metadataClasses): string
	{
		$sql = '';

		foreach ($metadataClasses as $class) {
			$sql .= sprintf("DROP TABLE IF EXISTS `%s`;\n", $class->getTableName());
		}

		return $sql;
	}

	private function createConnectionWithDatabase(DriverConnection $connection, string $dbname): DriverConnection
	{
		$connection->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s`; USE `%s`', $dbname, $dbname));

		return $connection;
	}

}
