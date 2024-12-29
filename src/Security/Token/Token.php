<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Token;

interface Token
{

	public function getId(): string;

	/**
	 * @return mixed[]
	 */
	public function getPayload(): array;

}
