<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

final class ForBlack implements Actor
{

	use ActorToTestTrait;

	protected function getName(): string
	{
		return 'black';
	}

	public function getRoles(): array
	{
		return ['ROLE_BLACK', 'ROLE_USER'];
	}

}
