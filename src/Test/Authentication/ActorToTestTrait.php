<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use LogicException;
use Shredio\Core\Intl\Language;
use Shredio\Core\Security\AccountId;

trait ActorToTestTrait
{

	private ?AccountId $id = null;

	public function __construct(
		private readonly Language $language = Language::English,
	)
	{
	}

	public function getId(): AccountId
	{
		return $this->id ?? throw new LogicException('Account ID is not filled');
	}

	public function getScalarId(): string|int
	{
		return $this->getId()->toOriginal();
	}

	public function setId(AccountId $id): void
	{
		$this->id = $id;
	}

	public function getLanguage(): Language
	{
		return $this->language;
	}

	public function getScalarLanguage(): string
	{
		return $this->getLanguage()->value;
	}

	public function getAuthorActor(): Actor
	{
		return $this;
	}

	public function getSignedActor(): Actor
	{
		return $this;
	}

	public function isFilled(): bool
	{
		return $this->id !== null;
	}

}
