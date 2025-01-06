<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

final class ForUser implements Actor
{

	use ActorToTestTrait;

	protected function getName(): string
	{
		return 'user';
	}

	public function getRoles(): array
	{
		return ['ROLE_USER'];
	}

}
