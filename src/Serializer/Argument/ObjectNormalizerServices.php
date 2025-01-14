<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer\Argument;

use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Security\UserContext;

final readonly class ObjectNormalizerServices
{

	public function __construct(
		public EntityManagerRegistry $managerRegistry,
		public UserContext $userContext,
	)
	{
	}

}
