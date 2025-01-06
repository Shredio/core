<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Security;

use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Security\AccountId;
use Shredio\Core\Security\UserContext;
use Shredio\Core\Security\UserEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class SymfonyUserContext implements UserContext
{

	public function __construct(
		private Security $security,
		private AppEnvironment $appEnv,
	)
	{
	}

	public function isGranted(string $attribute, ?object $subject = null): bool
	{
		return $this->security->isGranted($attribute, $subject);
	}

	public function requirePermission(string $attribute, ?object $subject = null, ?int $statusCode = null): void
	{
		if (!$this->security->isGranted($attribute, $subject)) {
			if ($statusCode === null || $statusCode === 403) {
				throw new AccessDeniedException();
			}

			throw new HttpException($statusCode, $this->appEnv->isDebugMode() ? 'Access Denied.' : '');
		}
	}

	public function getUserId(): AccountId
	{
		return $this->getUser()->getId();
	}

	public function getUserIdOrNull(): ?AccountId
	{
		return $this->getUserOrNull()?->getId();
	}

	public function getUser(): UserEntity
	{
		return $this->getUserOrNull() ?? throw new AccessDeniedException();
	}

	public function getUserOrNull(): ?UserEntity
	{
		/** @var UserEntity|null */
		return $this->security->getUser();
	}

}
