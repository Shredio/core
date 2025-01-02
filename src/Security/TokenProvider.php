<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use DateTimeInterface;
use Shredio\Core\Security\Token\Token;

interface TokenProvider
{

	public const string PayloadClaimKey = 'val';

	/**
	 * @param array<string, mixed> $defaultClaims
	 */
	public function setDefaultClaims(array $defaultClaims): void;

	public function load(string $id): ?Token;

	/**
	 * @param array<string, mixed> $payload
	 * @param array<string, mixed> $claims
	 */
	public function create(array $payload, ?DateTimeInterface $expiresAt = null, array $claims = []): Token;

}
