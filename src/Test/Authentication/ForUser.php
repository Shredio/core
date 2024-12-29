<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

final class ForUser implements Actor
{

	use ActorToTestTrait;

	public function getRoles(): array
	{
		return ['ROLE_USER'];
	}

}
