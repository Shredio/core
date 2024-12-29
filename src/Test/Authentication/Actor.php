<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Authentication;

use Shredio\Core\Intl\Language;
use Shredio\Core\Security\AccountId;

/**
 * If object is a collection of actors, it must return values of author actor.
 */
interface Actor
{

	public function getId(): AccountId;

	public function getScalarId(): string|int;

	public function getAuthorActor(): Actor;

	public function getSignedActor(): ?Actor;

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

}
