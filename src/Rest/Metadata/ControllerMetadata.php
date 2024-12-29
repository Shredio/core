<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Metadata;

use Shredio\Core\Rest\Attribute\Controller;
use Shredio\Core\Rest\Attribute\RouteParameter;
use Shredio\Core\Rest\Route\RoutePattern;
use InvalidArgumentException;

final readonly class ControllerMetadata
{

	/**
	 * @param class-string $className
	 * @param array<string, EndpointMetadata> $endpoints
	 * @param array<string, RouteParameter> $parameters
	 */
	public function __construct(
		public string $className,
		public string $name,
		public RoutePattern $pattern,
		public Controller $attribute,
		public array $endpoints,
		public array $parameters,
	)
	{
	}

	public function getRouteName(EndpointMetadata $endpoint, string $separator = '.'): string
	{
		return $this->name . $separator . $endpoint->name;
	}

	public function getEndpoint(string $name): EndpointMetadata
	{
		if (!isset($this->endpoints[$name])) {
			throw new InvalidArgumentException("Endpoint '$name' not found in controller '$this->className'");
		}

		return $this->endpoints[$name];
	}

	public function findEndpoint(string $name, string $instanceOf): EndpointMetadata
	{
		$endpoint = $this->getEndpoint($name);

		if (!$endpoint->attribute instanceof $instanceOf) {
			throw new InvalidArgumentException("Endpoint '$name' in controller '$this->className' is not instance of '$instanceOf'");
		}

		return $endpoint;
	}

}
