<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

interface UserContext
{

	public function getUser(): UserEntity;

	public function getUserOrNull(): ?UserEntity;

}
