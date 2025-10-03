<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use Shredio\Core\Security\Token\Token;
use Symfony\Component\Clock\DatePoint;

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
		return $this->tokenProvider->create(['id' => $id], new DatePoint($this->expiration));
	}

	/**
	 * @param string[] $roles
	 */
	public function createForApi(string|int $id, array $roles): Token
	{
		return $this->tokenProvider->create(
			['id' => $id, 'roles' => $roles],
			new DatePoint($this->expiration),
			['aud' => 'api'],
		);
	}

	public function load(string $id): ?Token
	{
		return $this->tokenProvider->load($id);
	}

}
