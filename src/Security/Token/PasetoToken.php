<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Token;

final readonly class PasetoToken implements Token
{

	/**
	 * @param array<string, mixed> $payload
	 * @param array<string, mixed> $claims
	 */
	public function __construct(
		public string $id,
		public array $payload,
		public array $claims = [],
	)
	{
	}

	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getPayload(): array
	{
		return $this->payload;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getClaims(): array
	{
		return $this->claims;
	}

	public function __toString(): string
	{
		return $this->id;
	}

}
