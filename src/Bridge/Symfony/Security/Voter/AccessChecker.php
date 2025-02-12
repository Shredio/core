<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Security\Voter;

use Shredio\Core\Security\AccountId;
use Shredio\Core\Security\UserEntity;
use Shredio\Voter\Service\VoterService;

final class AccessChecker extends VoterService
{

	public function isSuperAdmin(): bool
	{
		return $this->isGranted('ROLE_FOUNDER');
	}

	public function isGranted(string $attribute, ?object $object = null): bool
	{
		return $this->context->accessDecisionManager->decide($this->context->token, [$attribute], $object);
	}

	public function isBlack(): bool
	{
		return $this->isGranted('ROLE_BLACK');
	}

	public function areAccountsEqual(?AccountId $sourceId): bool
	{
		if (!$this->context->user || !$sourceId) {
			return false;
		}

		return $this->context->getUser(UserEntity::class)->getId()->equals($sourceId);
	}

}
