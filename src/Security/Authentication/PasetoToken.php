<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Authentication;

use DateTimeInterface;

final readonly class PasetoToken implements Token
{

	/**
	 * @param mixed[] $payload
	 */
	public function __construct(
		public string $id,
		public array $payload,
		public ?DateTimeInterface $expiresAt = null,
	)
	{
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getExpiresAt(): ?DateTimeInterface
	{
		return $this->expiresAt;
	}

	/**
	 * @return mixed[]
	 */
	public function getPayload(): array
	{
		return $this->payload;
	}

}
