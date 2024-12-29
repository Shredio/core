<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use Shredio\Core\Security\AccountId;

interface StaticActorToTest
{

	public function getId(): ?AccountId;

	/**
	 * @return string[]
	 */
	public function getRoles(): array;

}
