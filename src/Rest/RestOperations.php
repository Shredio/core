<?php declare(strict_types = 1);

namespace Shredio\Core\Rest;

use Psr\Http\Message\ResponseInterface;

interface RestOperations
{

	public const int NoGuard = 0;
	public const int GuardOnEntity = 1;
	public const int GuardOnAttribute = 2;
	public const int GuardOnEntityAndAttribute = self::GuardOnEntity | self::GuardOnAttribute;

	/**
	 * @param mixed[] $values
	 */
	public function create(array $values, int $guardMode = self::GuardOnAttribute): ResponseInterface;

	public function read(mixed $id, int $guardMode = self::GuardOnEntity): ResponseInterface;

	/**
	 * @param mixed[] $values
	 */
	public function update(mixed $id, array $values, int $guardMode = self::GuardOnEntity): ResponseInterface;

	public function delete(mixed $id, int $guardMode = self::GuardOnEntity): ResponseInterface;


}
