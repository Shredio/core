<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use Shredio\Core\Intl\Language;
use Shredio\Core\Security\AccountId;
use Stringable;

/**
 * If object is a collection of actors, it must return values of author actor.
 */
interface Actor extends Stringable
{

	public function getId(): AccountId;

	public function getScalarId(): string|int;

	public function getAuthorActor(): Actor;

	public function getSignedActor(): ?Actor;

	public function hasAuthor(): bool;

	/**
	 * Checks if the author actor is the same as signed actor.
	 */
	public function isSame(): bool;

	/**
	 * @return string[]
	 */
	public function getRoles(): array;

	public function getLanguage(): Language;

	public function getScalarLanguage(): string;

	/**
	 * @internal
	 */
	public function setId(AccountId $id): void;

	/**
	 * @internal
	 */
	public function isFilled(): bool;

	public function copy(): static;

	public function toString(bool $author = true, bool $signed = true): string;

	public function __toString(): string;

}
