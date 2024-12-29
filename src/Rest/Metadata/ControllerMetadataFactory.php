<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Metadata;

use ReflectionClass;

interface ControllerMetadataFactory
{

	/**
	 * @param ReflectionClass<object> $reflection
	 */
	public function create(ReflectionClass $reflection): ?ControllerMetadata;

}
