<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilderFactory;
use Shredio\Core\Cache\CacheFactory;
use Shredio\RapidDatabaseOperations\RapidOperationFactory;

final readonly class DoctrineRepositoryServices
{

	public function __construct(
		public ManagerRegistry $managerRegistry,
		public RapidOperationFactory $rapidOperationFactory,
		public DoctrineRepositoryHelper $helper,
		public CacheFactory $cacheFactory,
		public QueryBuilderFactory $queryBuilderFactory,
	)
	{
	}

}
