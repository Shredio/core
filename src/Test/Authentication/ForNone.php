<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use LogicException;
use Shredio\Core\Intl\Language;
use Shredio\Core\Security\AccountId;

final class ForNone implements Actor
{

	public function getId(): AccountId
	{
		throw new LogicException('Cannot get ID for none actor.');
	}

	public function getScalarId(): string|int
	{
		throw new LogicException('Cannot get scalar ID for none actor.');
	}

	public function getAuthorActor(): Actor
	{
		return $this;
	}

	public function getSignedActor(): ?Actor
	{
		return null;
	}

	public function setId(AccountId $id): void
	{
	}

	public function isFilled(): bool
	{
		return true;
	}

	public function getRoles(): array
	{
		throw new LogicException('Cannot get roles for none actor.');
	}

	public function getLanguage(): Language
	{
		throw new LogicException('Cannot get language for none actor.');
	}

	public function getScalarLanguage(): string
	{
		throw new LogicException('Cannot get scalar language for none actor.');
	}

	public function hasAuthor(): bool
	{
		return false;
	}

	public function copy(): static
	{
		return $this;
	}

	public function toString(bool $author = true, bool $signed = true): string
	{
		if ($author && $signed) {
			return 'no author and unsigned user';
		} elseif ($signed) {
			return 'unsigned user';
		} else {
			return 'no author';
		}
	}

	public function __toString(): string
	{
		return $this->toString();
	}

}
