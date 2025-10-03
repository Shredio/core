<?php declare(strict_types = 1);

namespace Shredio\Core\Serializer\Argument;

use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;

final readonly class ObjectNormalizerServices
{

	public function __construct(
		public EntityManagerRegistry $managerRegistry,
	)
	{
	}

}
