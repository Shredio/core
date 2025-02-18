<?php declare(strict_types = 1);

namespace Shredio\Core\Rest;

use Psr\Http\Message\ResponseInterface;

/**
 * @template T of object
 */
interface RestOperations
{

	public const string SerializationContext = 'serializationContext';
	public const string BeforeFlush = 'beforeFlush';
	public const string AfterFlush = 'afterFlush';

	public const int NoGuard = 0;
	public const int GuardOnEntity = 1;
	public const int GuardOnAttribute = 2;
	public const int GuardOnEntityAndAttribute = self::GuardOnEntity | self::GuardOnAttribute;

	/**
	 * Default guard mode is GuardOnAttribute, if guardNamespace is not set then NoGuard.
	 *
	 * @param mixed[] $values
	 * @param mixed[] $options
	 */
	public function create(array $values, ?int $guardMode = null, array $options = []): ResponseInterface;

	/**
	 * Default guard mode is GuardOnEntity, if guardNamespace is not set then NoGuard.
	 *
	 * @param mixed[] $options
	 */
	public function read(mixed $id, ?int $guardMode = null, array $options = []): ResponseInterface;

	/**
	 * Default guard mode is GuardOnEntity, if guardNamespace is not set then NoGuard.
	 *
	 * @param mixed[] $values
	 * @param mixed[] $options
	 */
	public function update(mixed $id, array $values, ?int $guardMode = null, array $options = []): ResponseInterface;

	/**
	 * Default guard mode is GuardOnEntity, if guardNamespace is not set then NoGuard.
	 *
	 * @param mixed[] $options
	 */
	public function delete(mixed $id, ?int $guardMode = null, array $options = []): ResponseInterface;

	/**
	 * @param mixed[] $values
	 * @return RestOperationBuilder<T>
	 */
	public function buildCreate(array $values): RestOperationBuilder;

	/**
	 * @return RestOperationBuilder<T>
	 */
	public function buildRead(mixed $id): RestOperationBuilder;

	/**
	 * @param mixed[] $values
	 * @return RestOperationBuilder<T>
	 */
	public function buildUpdate(mixed $id, array $values): RestOperationBuilder;

	/**
	 * @return RestOperationBuilder<T>
	 */
	public function buildDelete(mixed $id): RestOperationBuilder;

}
