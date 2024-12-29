<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Repository;

use Shredio\Core\Bridge\Doctrine\Repository\Trait\DoctrineRepositoryTrait;
use Symfony\Contracts\Service\ResetInterface;

abstract class DoctrineMutableRepository implements ResetInterface
{

	use DoctrineRepositoryTrait;

}
