<?php declare(strict_types = 1);

namespace Shredio\Core\Rest;

use Psr\Http\Message\ResponseInterface;

/**
 * @template T of object
 */
interface RestOperations
{

	public const string ValidationMode = 'validationMode';
	public const string SerializationContext = 'serializationContext';
	public const string BeforeFlush = 'beforeFlush';
	public const string AfterFlush = 'afterFlush';
	public const string OnEntity = 'onEntity';
	public const string StagingSimpleJson = 'stagingSimpleJson';
	public const string InstructionBeforeSerialization = 'instructionBeforeSerialization';
	public const string InstructionAfterSerialization = 'instructionAfterSerialization';

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
	 * Finds a single entity by a set of criteria. Default guard mode is GuardOnEntity, if guardNamespace is not set then NoGuard.
	 *
	 * @param array<string, mixed> $criteria
	 * @param array<string, 'ASC'|'DESC'> $orderBy
	 * @param mixed[] $options
	 */
	public function findOne(array $criteria, array $orderBy = [], ?int $guardMode = null, array $options = []): ResponseInterface;

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
	 * @param array<string, mixed> $criteria
	 * @param array<string, 'ASC'|'DESC'> $orderBy
	 * @return RestOperationBuilder<T>
	 */
	public function buildFindOne(array $criteria, array $orderBy = []): RestOperationBuilder;

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
