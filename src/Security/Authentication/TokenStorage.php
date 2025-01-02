<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Authentication;

use DateTimeInterface;

/**
 * @deprecated Use Shredio\Core\Security\* instead.
 */
interface TokenStorage
{

	/**
	 * @param mixed[] $payload
	 */
	public function create(array $payload, ?DateTimeInterface $expiresAt = null): Token;

	public function load(string $id): ?Token;

}
