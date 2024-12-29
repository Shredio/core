<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Metadata;

use RuntimeException;
use Shredio\Core\Rest\Attribute\Endpoint;
use ReflectionMethod;
use Shredio\Core\Common\Reflection\ReflectionHelper;
use Shredio\Core\Rest\Attribute\RouteParameter;

interface EndpointMetadataFactory
{

	public function create(ReflectionMethod $reflection): ?EndpointMetadata;

}
