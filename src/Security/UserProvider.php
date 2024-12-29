<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

interface UserProvider
{

	public function getUser(): UserEntity;

	public function getUserOrNull(): ?UserEntity;

}
