<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use DateTimeInterface;
use Shredio\Core\Security\Token\Token;

interface TokenProvider
{

	public function load(string $id): ?Token;

	/**
	 * @param mixed[] $payload
	 */
	public function create(array $payload, ?DateTimeInterface $expiresAt = null): Token;

}
