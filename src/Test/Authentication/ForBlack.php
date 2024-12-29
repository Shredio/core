<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

final class ForBlack implements Actor
{

	use ActorToTestTrait;

	public function getRoles(): array
	{
		return ['ROLE_BLACK', 'ROLE_USER'];
	}

}
