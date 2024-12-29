<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\Persistence\ManagerRegistry;

final readonly class SchemaMiddleware implements Middleware
{

	public function __construct(
		private ManagerRegistry $registry,
	)
	{
	}

	public function wrap(Driver $driver): Driver
	{
		return new SchemaDriver($driver, $this->registry);
	}

}
