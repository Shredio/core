<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use Shredio\Core\Security\AccountId;

final readonly class ForStaticBlackToTest implements StaticActorToTest
{

	public function __construct(
		private ?int $id = null,
	)
	{
	}

	public function getId(): ?AccountId
	{
		return $this->id === null ? null : AccountId::from($this->id);
	}

	/**
	 * @return string[]
	 */
	public function getRoles(): array
	{
		return ['ROLE_USER', 'ROLE_BLACK'];
	}

}
