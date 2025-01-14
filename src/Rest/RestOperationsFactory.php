<?php declare(strict_types = 1);

namespace Shredio\Core\Rest;

interface RestOperationsFactory
{

	/**
	 * @template T of object
	 * @param class-string<T> $entityName
	 * @param mixed[] $defaultOptions
	 * @return RestOperations<T>
	 */
	public function create(string $entityName, ?string $guardNamespace = null, array $defaultOptions = []): RestOperations;

}
