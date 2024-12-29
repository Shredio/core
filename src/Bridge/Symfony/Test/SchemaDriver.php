<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use SensitiveParameter;

final class SchemaDriver extends AbstractDriverMiddleware
{

	public function __construct(
		Driver $wrappedDriver,
		private readonly ManagerRegistry $registry,
	)
	{
		parent::__construct($wrappedDriver);
	}

	public function connect(#[SensitiveParameter] array $params): DriverConnection
	{
		$connection = parent::connect($params);

		/** @var string|null $connectionKey */
		$connectionKey = $params['dama.connection_key'] ?? null; // @phpstan-ignore-line

		if ($connectionKey) {
			/** @var EntityManagerInterface $em */
			$em = $this->registry->getManager($connectionKey);
			$schemaTool = new SchemaTool($em);
			$sql = implode(
				';',
				$schemaTool->getCreateSchemaSql($em->getMetadataFactory()->getAllMetadata()),
			);

			$connection->exec($sql);
		}

		return $connection;
	}

}
