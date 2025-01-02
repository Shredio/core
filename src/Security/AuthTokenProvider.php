<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use DateTimeImmutable;
use Shredio\Core\Intl\Language;
use Shredio\Core\Security\Token\Token;

final readonly class AuthTokenProvider
{

	public function __construct(
		private TokenProvider $tokenProvider,
		private string $expiration = '+ 2 months',
	)
	{
	}

	public function withExpiration(string $expiration): self
	{
		return new self($this->tokenProvider, $expiration);
	}

	public function create(string|int $id): Token
	{
		return $this->tokenProvider->create(['id' => $id], new DateTimeImmutable($this->expiration));
	}

	/**
	 * @param string[] $roles
	 */
	public function createForApi(string|int $id, array $roles, Language $language): Token
	{
		return $this->tokenProvider->create(
			['id' => $id, 'roles' => $roles, 'language' => $language->value],
			new DateTimeImmutable($this->expiration),
			['aud' => 'api'],
		);
	}

	public function load(string $id): ?Token
	{
		return $this->tokenProvider->load($id);
	}

}
