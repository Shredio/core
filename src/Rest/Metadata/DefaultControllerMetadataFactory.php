<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Metadata;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Shredio\Core\Common\Reflection\ReflectionHelper;
use Shredio\Core\Rest\Attribute\Controller;
use Shredio\Core\Rest\Attribute\RouteParameter;
use Shredio\Core\Rest\Route\RoutePattern;

final readonly class DefaultControllerMetadataFactory implements ControllerMetadataFactory
{

	public function __construct(
		private EndpointMetadataFactory $endpointMetadataFactory,
	)
	{
	}

	/**
	 * @param ReflectionClass<object> $reflection
	 */
	public function create(ReflectionClass $reflection): ?ControllerMetadata
	{
		$controller = ReflectionHelper::getAttribute($reflection, Controller::class);

		if (!$controller) {
			return null;
		}

		$name = $controller->name ?? $this->getControllerName($reflection->getShortName());

		$pattern = new RoutePattern($controller->pattern ?? RoutePattern::camelCaseToKebabCase($name), $controller->id);
		$endpoints = [];

		foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			$endpoint = $this->endpointMetadataFactory->create($method);

			if ($endpoint) {
				if (isset($endpoints[$endpoint->name])) {
					throw new RuntimeException(
						sprintf('Duplicate endpoint name %s in %s', $endpoint->name, $reflection->name)
					);
				}

				$endpoints[$endpoint->name] = $endpoint;
			}
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

		return new ControllerMetadata($reflection->name, $name, $pattern, $controller, $endpoints, $parameters);
	}

	private function getControllerName(string $className): string
	{
		if (str_ends_with($className, 'Controller')) {
			$className = substr($className, 0, -10);
		}

		return lcfirst($className);
	}

}
