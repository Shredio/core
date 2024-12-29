<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Metadata;

use ReflectionMethod;
use RuntimeException;
use Shredio\Core\Common\Reflection\ReflectionHelper;
use Shredio\Core\Rest\Attribute\Endpoint;
use Shredio\Core\Rest\Attribute\RouteParameter;

final readonly class DefaultEndpointMetadataFactory implements EndpointMetadataFactory
{

	public function create(ReflectionMethod $reflection): ?EndpointMetadata
	{
		$attribute = ReflectionHelper::getAttribute($reflection, Endpoint::class, true);

		if (!$attribute) {
			return null;
		}

		$parameters = [];

		foreach (ReflectionHelper::getAttributes($reflection, RouteParameter::class) as $parameter) {
			if (isset($parameters[$parameter->name])) {
				throw new RuntimeException(
					sprintf('Duplicate parameter name %s in %s', $parameter->name, $reflection->name)
				);
			}

			$parameters[$parameter->name] = $parameter;
		}

		return new EndpointMetadata($reflection->name, $attribute, $parameters);
	}

}
