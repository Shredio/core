<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Token;

final readonly class PasetoToken implements Token
{

	/**
	 * @param mixed[] $payload
	 */
	public function __construct(
		public string $id,
		public array $payload,
	)
	{
	}

	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return mixed[]
	 */
	public function getPayload(): array
	{
		return $this->payload;
	}

}
