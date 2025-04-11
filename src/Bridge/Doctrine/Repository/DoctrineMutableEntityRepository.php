<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Repository;

use Shredio\Core\Bridge\Doctrine\Repository\Trait\DoctrineEntityRepositoryTrait;
use Shredio\Core\Exception\RecordNotFoundException;

/**
 * @template TEntity of object
 * @template TException of RecordNotFoundException
 * @template TId
 */
abstract class DoctrineMutableEntityRepository
{

	/** @use DoctrineEntityRepositoryTrait<TEntity, TException, TId> */
	use DoctrineEntityRepositoryTrait;

}
