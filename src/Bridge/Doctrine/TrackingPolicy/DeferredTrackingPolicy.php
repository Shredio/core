<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\TrackingPolicy;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

#[AsDoctrineListener(Events::loadClassMetadata)]
final class DeferredTrackingPolicy
{

	public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
	{
		$classMetadata = $args->getClassMetadata();
		$classMetadata->setChangeTrackingPolicy(ClassMetadata::CHANGETRACKING_DEFERRED_EXPLICIT);
	}

}
