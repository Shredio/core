<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Security;

use Shredio\Core\Security\UserEntity;
use Shredio\Core\Security\UserContext;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class SymfonyUserContext implements UserContext
{

	public function __construct(
		private Security $security,
	)
	{
	}

	public function getUser(): UserEntity
	{
		return $this->getUserOrNull() ?? throw new AccessDeniedException('User is not authenticated.');
	}

	public function getUserOrNull(): ?UserEntity
	{
		/** @var UserEntity|null */
		return $this->security->getUser();
	}

}
