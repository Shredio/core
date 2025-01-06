<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use Shredio\Core\Intl\Language;
use Shredio\Core\Security\AccountId;

final readonly class ForDistinct implements Actor
{

	private Actor $signed;

	public function __construct(
		private Actor $author,
		Actor $signed = null,
	)
	{
		$this->signed = $signed ?? $this->author->copy();
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

	public function getSignedActor(): Actor
	{
		return $this->signed;
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
		return new self(
			$this->author->copy(),
			$this->signed->copy(),
		);
	}

	public function toString(bool $author = true, bool $signed = true): string
	{
		if ($author && $signed) {
			return $this->author->toString(true, false) . ' and ' . $this->signed->toString(false);
		} else if ($signed) {
			return $this->signed->toString(false);
		} else {
			return $this->author->toString(true, false);
		}
	}

	public function __toString(): string
	{
		return $this->toString();
	}

}
