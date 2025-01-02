<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Metadata;

use Shredio\Core\Rest\Attribute\CreateEndpoint;
use Shredio\Core\Rest\Attribute\DeleteEndpoint;
use Shredio\Core\Rest\Attribute\Endpoint;
use Shredio\Core\Rest\Attribute\ReadCollectionEndpoint;
use Shredio\Core\Rest\Attribute\ReadEndpoint;
use Shredio\Core\Rest\Attribute\RouteParameter;
use Shredio\Core\Rest\Attribute\UpdateEndpoint;
use Shredio\Core\Rest\Route\RoutePattern;
use RuntimeException;

final readonly class EndpointMetadata
{

	/**
	 * @param array<string, RouteParameter> $parameters
	 */
	public function __construct(
		public string $name,
		public Endpoint $attribute,
		public array $parameters,
	)
	{
	}

	public function isCreate(): bool
	{
		return $this->attribute instanceof CreateEndpoint;
	}

	public function isRead(): bool
	{
		return $this->attribute instanceof ReadEndpoint;
	}

	public function isUpdate(): bool
	{
		return $this->attribute instanceof UpdateEndpoint;
	}

	public function isDelete(): bool
	{
		return $this->attribute instanceof DeleteEndpoint;
	}

	public function isReadCollection(): bool
	{
		return $this->attribute instanceof ReadCollectionEndpoint;
	}

	public function getPattern(ControllerMetadata $controllerMetadata): RoutePattern
	{
		if ($this->attribute->hasAbsolutePath()) {
			$route = $this->attribute->route;

			if (!$route) {
				throw new RuntimeException(
					sprintf('Route is required for absolute path in %s::%s', $controllerMetadata->className, $this->name)
				);
			}

			$pattern = new RoutePattern($route);

			if ($controllerMetadata->basePath !== '/') {
				$pattern = $pattern->withPrependedPattern($controllerMetadata->basePath);
			}

			return $pattern;
		}

		$pattern = $controllerMetadata->pattern;

		if ($this->attribute->isParametrized()) {
			$pattern = $pattern->createParametrizedRoute();
		}

		if ($this->attribute->route) {
			$pattern = $pattern->withAppendedPattern($this->attribute->route);
		} else if ($this->name !== $this->attribute::getDefaultMethodName()) {
			$pattern = $pattern->withAppendedPattern(RoutePattern::camelCaseToKebabCase($this->name));
		}

		return $pattern;
	}

	/**
	 * @return array<string, RouteParameter>
	 */
	public function getParameters(ControllerMetadata $controllerMetadata): array
	{
		$parameters = $controllerMetadata->parameters;

		foreach ($this->parameters as $name => $parameter) {
			$parameters[$name] = $parameter;
		}

		return $parameters;
	}

}
