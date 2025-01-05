<?php declare(strict_types = 1);

namespace Shredio\Core\Rest;

use Psr\Http\Message\ResponseInterface;

interface RestOperations
{

	public const string SerializationContext = 'serializationContext';

	public const int NoGuard = 0;
	public const int GuardOnEntity = 1;
	public const int GuardOnAttribute = 2;
	public const int GuardOnEntityAndAttribute = self::GuardOnEntity | self::GuardOnAttribute;

	/**
	 * @param mixed[] $values
	 * @param mixed[] $options
	 */
	public function create(array $values, int $guardMode = self::GuardOnAttribute, array $options = []): ResponseInterface;

	/**
	 * @param mixed[] $options
	 */
	public function read(mixed $id, int $guardMode = self::GuardOnEntity, array $options = []): ResponseInterface;

	/**
	 * @param mixed[] $values
	 * @param mixed[] $options
	 */
	public function update(mixed $id, array $values, int $guardMode = self::GuardOnEntity, array $options = []): ResponseInterface;

	/**
	 * @param mixed[] $options
	 */
	public function delete(mixed $id, int $guardMode = self::GuardOnEntity, array $options = []): ResponseInterface;


}
