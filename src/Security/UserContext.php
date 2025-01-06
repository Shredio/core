<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

interface UserContext
{

	public function isGranted(string $attribute, ?object $subject = null): bool;

	public function requirePermission(string $attribute, ?object $subject = null, ?int $statusCode = null): void;

	public function getUserId(): AccountId;

	public function getUserIdOrNull(): ?AccountId;

	public function getUser(): UserEntity;

	public function getUserOrNull(): ?UserEntity;

}
