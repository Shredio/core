<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Authentication;

use DateTimeInterface;

interface Token
{

	public function getId(): string;

	public function getExpiresAt(): ?DateTimeInterface;

	/**
	 * @return mixed[]
	 */
	public function getPayload(): array;

}
