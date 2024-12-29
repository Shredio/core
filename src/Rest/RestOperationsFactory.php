<?php declare(strict_types = 1);

namespace Shredio\Core\Rest;

interface RestOperationsFactory
{

	/**
	 * @param class-string $entityName
	 */
	public function create(string $entityName, ?string $guardNamespace = null): RestOperations;

}
