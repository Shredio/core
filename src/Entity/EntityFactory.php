<?php declare(strict_types = 1);

namespace Shredio\Core\Entity;

use Shredio\Core\Exception\HttpException;

interface EntityFactory
{

	/**
	 * Create an entity object from the given class name and data.
	 *
	 * @template TEntity of object
	 * @param class-string<TEntity> $className
	 * @param mixed[] $data
	 * @return TEntity
	 *
	 * @throws HttpException
	 */
	public function create(string $className, array $data): object;

	/**
	 * Update an entity object with the given data.
	 *
	 * @template TEntity of object
	 * @param TEntity $entity
	 * @param mixed[] $data
	 * @return TEntity
	 *
	 * @throws HttpException
	 */
	public function update(object $entity, array $data): object;

}
