<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Authentication;

use DateTimeInterface;

interface TokenStorage
{

	/**
	 * @param mixed[] $payload
	 */
	public function create(array $payload, ?DateTimeInterface $expiresAt = null): Token;

	public function load(string $id): ?Token;

}
