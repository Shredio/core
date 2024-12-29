<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilderFactory;
use Shredio\Core\Cache\CacheFactory;
use Shredio\Core\Database\Rapid\EntityRapidOperationFactory;

final readonly class DoctrineRepositoryServices
{

	public function __construct(
		public ManagerRegistry $managerRegistry,
		public EntityRapidOperationFactory $rapidOperationFactory,
		public DoctrineRepositoryHelper $helper,
		public CacheFactory $cacheFactory,
		public QueryBuilderFactory $queryBuilderFactory,
	)
	{
	}

}
