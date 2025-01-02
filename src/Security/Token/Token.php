<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Token;

use Stringable;

interface Token extends Stringable
{

	public function getId(): string;

	/**
	 * @return array<string, mixed>
	 */
	public function getPayload(): array;

	/**
	 * @return array<string, mixed>
	 */
	public function getClaims(): array;

}
