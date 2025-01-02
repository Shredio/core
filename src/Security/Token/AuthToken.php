<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Token;

final readonly class AuthToken implements Token
{

	public function __construct(
		private Token $token,
	)
	{
	}

	public function getId(): string
	{
		return $this->token->getId();
	}

	public function getPayload(): array
	{
		return $this->token->getPayload();
	}

	public function getClaims(): array
	{
		return $this->token->getClaims();
	}

	public function __toString(): string
	{
		return (string) $this->token;
	}

}
