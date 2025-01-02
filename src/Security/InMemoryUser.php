<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use Shredio\Core\Intl\Language;

final readonly class InMemoryUser implements UserEntity
{

	private AccountId $id;

	/** @var string[] */
	private array $roles;

	/**
	 * @param string[] $roles
	 */
	public function __construct(
		string|int $id,
		array $roles,
		private Language $language,
	)
	{
		$this->id = AccountId::from($id);
		$this->roles = array_map(strtoupper(...), $roles);
	}

	public function getId(): AccountId
	{
		return $this->id;
	}

	public function getLanguage(): Language
	{
		return $this->language;
	}

	public function getRoles(): array
	{
		return $this->roles;
	}

	public function eraseCredentials(): void
	{
	}

	/**
	 * @return non-empty-string
	 */
	public function getUserIdentifier(): string
	{
		/** @var non-empty-string */
		return $this->id->toString();
	}

}
