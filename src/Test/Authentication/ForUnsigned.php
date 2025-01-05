<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use Shredio\Core\Intl\Language;
use Shredio\Core\Security\AccountId;

final readonly class ForUnsigned implements Actor
{

	public function __construct(
		private Actor $author,
	)
	{
	}

	public function getId(): AccountId
	{
		return $this->author->getId();
	}

	public function getScalarId(): string|int
	{
		return $this->getId()->toOriginal();
	}

	public function setId(AccountId $id): void
	{
		$this->author->setId($id);
	}

	public function getAuthorActor(): Actor
	{
		return $this->author;
	}

	public function getSignedActor(): null
	{
		return null;
	}

	public function getRoles(): array
	{
		return $this->author->getRoles();
	}

	public function getLanguage(): Language
	{
		return $this->author->getLanguage();
	}

	public function getScalarLanguage(): string
	{
		return $this->getLanguage()->value;
	}

	public function isFilled(): bool
	{
		return $this->author->isFilled();
	}

	public function hasAuthor(): bool
	{
		return true;
	}

	public function copy(): static
	{
		return new self($this->author->copy());
	}

}
