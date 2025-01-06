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

	abstract protected function getName(): string;

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

	public function hasAuthor(): bool
	{
		return true;
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

	public function copy(): static
	{
		$copy = clone $this;
		unset($copy->id);

		return $copy;
	}

	public function isSame(): bool
	{
		return true;
	}

	public function toString(bool $author = true, bool $signed = true): string
	{
		$name = $this->getName();

		if ($author && $signed) {
			return sprintf('%s author and signed as %s', $name, $name);
		} elseif ($signed) {
			return sprintf('signed as %s', $name);
		} else {
			return sprintf('%s author', $name);
		}
	}

	public function __toString(): string
	{
		return $this->toString();
	}

}
